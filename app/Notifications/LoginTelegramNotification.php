<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoginTelegramNotification extends Notification
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
            'type' => $this->data['type'] ?? 'info',
            'title' => $this->data['title'] ?? 'Aktivitas Login',
            'message' => $this->data['message'] ?? '',
            'user_name' => $this->data['user_name'] ?? null,
            'user_email' => $this->data['user_email'] ?? null,
            'login_time' => $this->data['login_time'] ?? null,
            'ip_address' => $this->data['ip_address'] ?? null,
            'user_agent' => $this->data['user_agent'] ?? null,
            'login_method' => $this->data['login_method'] ?? null,
        ];
    }
}