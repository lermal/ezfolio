<?php

namespace App\Listeners;

use App\Events\NewMessage;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class NewMessageListener
{
    public function __construct(
        public TelegramService $telegramService
    ) {
        //
    }

    public function handle(NewMessage $event): void
    {
        Log::info('NewMessageListener called', [
            'name' => $event->name,
            'email' => $event->email,
            'subject' => $event->subject,
            'body' => $event->body,
            'created_at' => $event->createdAt
        ]);
        
        $message = "Новое сообщение от: " . $event->name . "\n" .
                   "Email: " . $event->email . "\n" .
                   "Тема: " . $event->subject . "\n" .
                   "Сообщение: " . $event->body;

        Log::info('Telegram message prepared: ' . $message);

        try {
            $result = $this->telegramService->sendMessage(
                env('TELEGRAM_CHAT_ID'),
                $message
            );
            
            Log::info('Telegram message sent successfully', ['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Failed to send telegram message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
