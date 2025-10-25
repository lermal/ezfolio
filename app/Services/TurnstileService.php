<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    private string $secretKey;
    private string $siteKey;
    private string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        $this->secretKey = env('TURNSTILE_SECRET_KEY', '');
        $this->siteKey = env('TURNSTILE_SITE_KEY', '');
    }

    /**
     * Проверить токен Turnstile
     *
     * @param string $token
     * @param string|null $remoteIp
     * @return array
     */
    public function verify(string $token, ?string $remoteIp = null): array
    {
        try {
            if (empty($this->secretKey)) {
                return [
                    'success' => false,
                    'message' => 'Turnstile не настроен'
                ];
            }

            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => 'Токен капчи не предоставлен'
                ];
            }

            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp
            ]);

            if (!$response->successful()) {
                Log::error('Turnstile API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Ошибка при проверке капчи'
                ];
            }

            $data = $response->json();

            if (!$data['success']) {
                $errorCodes = $data['error-codes'] ?? [];
                $errorMessage = $this->getErrorMessage($errorCodes);

                // Логируем ошибки Turnstile для отладки
                Log::warning('Turnstile verification failed', [
                    'error_codes' => $errorCodes,
                    'error_message' => $errorMessage,
                    'remote_ip' => $remoteIp,
                    'token_length' => strlen($token)
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error_codes' => $errorCodes
                ];
            }

            // Логируем успешную проверку для мониторинга
            Log::info('Turnstile verification successful', [
                'remote_ip' => $remoteIp,
                'token_length' => strlen($token)
            ]);

            return [
                'success' => true,
                'message' => 'Капча пройдена успешно'
            ];

        } catch (\Throwable $th) {
            Log::error('Turnstile verification error', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при проверке капчи'
            ];
        }
    }

    /**
     * Получить сообщение об ошибке по кодам
     *
     * @param array $errorCodes
     * @return string
     */
    private function getErrorMessage(array $errorCodes): string
    {
        $messages = [
            'missing-input-secret' => 'Ошибка конфигурации капчи',
            'invalid-input-secret' => 'Ошибка конфигурации капчи',
            'missing-input-response' => 'Пожалуйста, пройдите проверку капчи',
            'invalid-input-response' => 'Неверный ответ капчи. Попробуйте еще раз',
            'bad-request' => 'Неверный запрос. Попробуйте еще раз',
            'timeout-or-duplicate' => 'Капча устарела или запрос был отправлен повторно. Пожалуйста, обновите капчу и попробуйте снова',
            'internal-error' => 'Временная ошибка сервера. Попробуйте еще раз через несколько секунд'
        ];

        if (empty($errorCodes)) {
            return 'Ошибка проверки капчи. Попробуйте еще раз';
        }

        $errorMessages = [];
        foreach ($errorCodes as $code) {
            if (isset($messages[$code])) {
                $errorMessages[] = $messages[$code];
            } else {
                $errorMessages[] = "Ошибка капчи: {$code}";
            }
        }

        return implode('. ', $errorMessages);
    }

    /**
     * Получить публичный ключ сайта
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Проверить, настроена ли капча
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->siteKey);
    }
}

