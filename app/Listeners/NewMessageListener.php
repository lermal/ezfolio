<?php

namespace App\Listeners;

use App\Events\NewMessage;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class NewMessageListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var array
     */
    public $backoff = [10, 60];

    public function __construct(
        public TelegramService $telegramService
    ) {
        //
    }

    public function handle(NewMessage $event): void
    {
        $chatId = config('services.telegram.chat_id');

        if (empty($chatId) || empty(config('services.telegram.bot_token'))) {
            return;
        }

        $message = "Новое сообщение от: " . e($event->name) . "\n" .
                   "Email: " . e($event->email) . "\n" .
                   "Тема: " . e($event->subject) . "\n" .
                   "Сообщение: " . e($event->body);

        $result = $this->telegramService->sendMessage($chatId, $message);

        if (empty($result['ok'])) {
            throw new RuntimeException('Telegram API error: ' . ($result['description'] ?? 'empty response'));
        }
    }

    public function failed(NewMessage $event, Throwable $exception): void
    {
        Log::error('Failed to send telegram message', [
            'error' => $exception->getMessage(),
            'email' => $event->email,
        ]);
    }
}
