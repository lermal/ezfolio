<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
class TelegramService
{
    public string $url = 'https://api.telegram.org/bot';
    public string $token;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN', config('app.bot_token'));
        $this->url = $this->url . $this->token . '/';
    }

    public function sendPhoto($chatId, $photoUrl, $caption = '', $markup = null, $fallbackUrl = null)
    {
        // Сначала пытаемся отправить основное изображение
        $response = $this->sendPhotoByUrl($chatId, $photoUrl, $caption, $markup);
        
        // Если получили ошибку 400 и есть fallback URL, попробуем его
        if ($response['status'] === 400 && $fallbackUrl && $fallbackUrl !== $photoUrl) {
            Log::warning('Telegram API returned 400 error, trying fallback image', [
                'original_url' => $photoUrl,
                'error' => $response['response']['description'] ?? 'Unknown error'
            ]);

            $fallbackResponse = $this->sendPhotoByUrl($chatId, $fallbackUrl, $caption, $markup);
            
            // Если и fallback не работает, отправляем текстовое сообщение
            if ($fallbackResponse['status'] === 400) {
                Log::warning('Fallback image also failed, sending text message instead', [
                    'fallback_url' => $fallbackUrl,
                    'caption' => $caption
                ]);
                
                return $this->sendMessage($chatId, $caption, $markup);
            }
            
            return $fallbackResponse;
        }

        // Если основное изображение не работает, отправляем текстовое сообщение
        if ($response['status'] === 400) {
            Log::warning('Photo sending failed, sending text message instead', [
                'photo_url' => $photoUrl,
                'caption' => $caption
            ]);
            
            return $this->sendMessage($chatId, $caption, $markup);
        }

        return $response;
    }

    private function sendPhotoByUrl($chatId, $photoUrl, $caption = '', $markup = null)
    {
        // Проверяем доступность изображения перед отправкой
        $imageCheck = $this->checkImageAvailability($photoUrl);
        
        if (!$imageCheck['available']) {
            Log::warning('Image not available, skipping photo send', [
                'photo_url' => $photoUrl,
                'check_result' => $imageCheck
            ]);
            
            return [
                'status' => 400,
                'response' => ['ok' => false, 'error_code' => 400, 'description' => 'Image not available']
            ];
        }

        // Проверяем размер изображения (Telegram ограничивает до 5MB)
        if (isset($imageCheck['content_length']) && $imageCheck['content_length'] > 5 * 1024 * 1024) {
            Log::warning('Image too large for Telegram, skipping photo send', [
                'photo_url' => $photoUrl,
                'size' => $imageCheck['content_length']
            ]);
            
            return [
                'status' => 400,
                'response' => ['ok' => false, 'error_code' => 400, 'description' => 'Image too large']
            ];
        }

        $data = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ];

        if($markup) {
            $data['reply_markup'] = json_encode(['inline_keyboard' => $markup]);
        }

        Log::debug('TelegramService sendPhoto request', [
            'chat_id' => $chatId,
            'photo_url' => $photoUrl,
            'caption' => $caption,
            'data' => $data
        ]);

        $response = Http::timeout(30)->post($this->url . 'sendPhoto', $data);
        $responseData = $response->json();

        Log::debug('TelegramService sendPhoto response', [
            'status' => $response->status(),
            'response' => $responseData
        ]);

        return [
            'status' => $response->status(),
            'response' => $responseData
        ];
    }


    public function checkImageAvailability($url)
    {
        try {
            $response = Http::head($url);
            
            Log::debug('Image availability check', [
                'url' => $url,
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length')
            ]);
            
            return [
                'available' => $response->successful(),
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length')
            ];
        } catch (\Exception $e) {
            Log::error('Error checking image availability', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendMessage($chatId, $message, $markup = null)
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        if($markup) {
            $data['reply_markup'] = json_encode(['inline_keyboard' => $markup]);
        }

        $response = Http::timeout(30)->post($this->url . 'sendMessage', $data);

        return $response->json();
    }

    public function answerCallbackQuery($callbackQueryId, $text)
    {
        // Быстрый ответ на callback query с коротким таймаутом
        $response = Http::timeout(5)->post($this->url . 'answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => true // Устанавливаем true, чтобы показать всплывающее окно
        ]);

        return $response->json();
    }

    public function deleteMessage($chatId, $messageId)
    {
        $response = Http::post($this->url . 'deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);

        return $response->json();
    }

    public function getChatMessages($chatId, $fromDate = null, $limit = 100)
    {
        try {
            // Получаем сообщения из чата через getUpdates
            // Это ограничение Telegram API - нет прямого метода получения истории сообщений
            // Но мы можем использовать getUpdates для получения последних сообщений
            
            $params = [
                'limit' => $limit,
                'timeout' => 0
            ];
            
            if ($fromDate) {
                $params['offset'] = -1; // Получаем все сообщения
            }
            
            $response = Http::timeout(30)->get($this->url . 'getUpdates', $params);
            
            if (!$response->successful()) {
                Log::error('Failed to get chat messages', [
                    'chat_id' => $chatId,
                    'response' => $response->body()
                ]);
                return [];
            }
            
            $updates = $response->json('result', []);
            $messages = [];
            
            foreach ($updates as $update) {
                if (isset($update['message']) && 
                    isset($update['message']['chat']['id']) && 
                    $update['message']['chat']['id'] == $chatId) {
                    
                    $message = $update['message'];
                    
                    // Фильтруем по дате если указана
                    if ($fromDate) {
                        $messageDate = \Carbon\Carbon::createFromTimestamp($message['date']);
                        if ($messageDate->lt($fromDate)) {
                            continue;
                        }
                    }
                    
                    $messages[] = [
                        'message_id' => $message['message_id'],
                        'from' => $message['from'] ?? null,
                        'text' => $message['text'] ?? '',
                        'date' => \Carbon\Carbon::createFromTimestamp($message['date']),
                        'chat_id' => $message['chat']['id']
                    ];
                }
            }
            
            return $messages;
            
        } catch (\Exception $e) {
            Log::error('Error getting chat messages: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'from_date' => $fromDate
            ]);
            return [];
        }
    }

    public function checkUserMessagesInChat($userId, $chatId, $fromDate)
    {
        try {
            $messages = $this->getChatMessages($chatId, $fromDate);
            
            foreach ($messages as $message) {
                if (isset($message['from']['id']) && $message['from']['id'] == $userId) {
                    return [
                        'found' => true,
                        'message' => $message['text'],
                        'date' => $message['date']
                    ];
                }
            }
            
            return ['found' => false];
            
        } catch (\Exception $e) {
            Log::error('Error checking user messages: ' . $e->getMessage(), [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'from_date' => $fromDate
            ]);
            return ['found' => false, 'error' => $e->getMessage()];
        }
    }

    public function editMessageText($chatId, $messageId, $text, $markup = null)
    {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if($markup) {
            $data['reply_markup'] = json_encode(['inline_keyboard' => $markup]);
        }

        $response = Http::timeout(30)->post($this->url . 'editMessageText', $data);

        return $response->json();
    }
}
