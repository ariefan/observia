<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HealthAlertTelegramNotification extends Notification
{
    use Queueable;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable): array
    {
        return [
            'type' => 'health',
            'title' => $this->data['title'] ?? 'Peringatan Kesehatan Ternak',
            'message' => $this->data['message'] ?? 'Ada masalah kesehatan yang memerlukan perhatian.',
            'livestock_name' => $this->data['livestock_name'] ?? null,
            'farm_name' => $this->data['farm_name'] ?? null,
            'created_by' => $this->data['created_by'] ?? null,
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}