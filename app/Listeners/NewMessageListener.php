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
        Log::debug('NewMessageListener called', $event);
        $message = "Новое сообщение от: " . $event->name . "\n" .
                   "Email: " . $event->email . "\n" .
                   "Тема: " . $event->subject . "\n" .
                   "Сообщение: " . $event->body;

        Log::debug('Message: ' . $message);

        $this->telegramService->sendMessage(
            env('TELEGRAM_CHAT_ID'),
            $message
        );
    }
}
