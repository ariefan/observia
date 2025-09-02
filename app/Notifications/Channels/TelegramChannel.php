<?php

namespace App\Notifications\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!$this->telegramService->isEnabled()) {
            return;
        }

        // Get the Telegram representation of the notification
        $message = $notification->toTelegram($notifiable);

        if (is_string($message)) {
            // Simple text message
            $this->telegramService->sendToDefaultChat($message);
        } elseif (is_array($message)) {
            // Formatted message with data
            if (isset($message['chat_id'])) {
                // Send to specific chat
                $chatId = $message['chat_id'];
                unset($message['chat_id']);
                $this->telegramService->sendFormattedNotification($message);
            } else {
                // Send to default chat
                $this->telegramService->sendFormattedNotification($message);
            }
        }
    }
}