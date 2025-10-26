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
        $message = "Новое сообщение от: " . $event->name . "\n" .
                   "Email: " . $event->email . "\n" .
                   "Тема: " . $event->subject . "\n" .
                   "Сообщение: " . $event->body;

        try {
            $result = $this->telegramService->sendMessage(
                env('TELEGRAM_CHAT_ID'),
                $message
            );
        } catch (\Exception $e) {
            Log::error('Failed to send telegram message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
