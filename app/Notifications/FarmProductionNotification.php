<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FarmProductionNotification extends Notification
{
    use Queueable;

    private string $type;
    private string $title;
    private string $message;
    private ?string $farmName;
    private ?string $actionUrl;

    public function __construct(
        string $type,
        string $title,
        string $message,
        ?string $farmName = null,
        ?string $actionUrl = null
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->farmName = $farmName;
        $this->actionUrl = $actionUrl;
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
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'farm_name' => $this->farmName,
            'action_url' => $this->actionUrl,
        ];
    }

    /**
     * Create a milk collection notification.
     */
    public static function milkCollection(
        string $batchCode,
        float $volume,
        string $farmName
    ): self {
        return new self(
            type: 'milk_collection',
            title: '🥛 Pengumpulan Susu Baru',
            message: "Batch {$batchCode} telah dikumpulkan.\nVolume: {$volume} liter",
            farmName: $farmName,
            actionUrl: "/milk-batches"
        );
    }

    /**
     * Create a milk batch status notification.
     */
    public static function milkBatchStatus(
        string $batchCode,
        string $status,
        string $farmName,
        ?string $reason = null
    ): self {
        $statusText = match ($status) {
            'received' => '✅ Diterima',
            'rejected' => '❌ Ditolak',
            'approved' => '✅ Disetujui',
            default => $status,
        };

        $message = "Batch {$batchCode}: {$statusText}";
        if ($reason) {
            $message .= "\nAlasan: {$reason}";
        }

        return new self(
            type: 'milk_batch_status',
            title: '📋 Status Batch Susu',
            message: $message,
            farmName: $farmName,
            actionUrl: "/milk-batches"
        );
    }

    /**
     * Create a quality test notification.
     */
    public static function qualityTest(
        string $batchCode,
        string $grade,
        string $farmName
    ): self {
        $gradeEmoji = match ($grade) {
            'A' => '🏆',
            'B' => '🥈',
            'C' => '🥉',
            'Reject' => '❌',
            default => '📊',
        };

        return new self(
            type: 'quality_test',
            title: '🔬 Hasil Uji Kualitas',
            message: "Batch {$batchCode}\nGrade: {$gradeEmoji} {$grade}",
            farmName: $farmName,
            actionUrl: "/milk-batches"
        );
    }

    /**
     * Create a cheese production notification.
     */
    public static function cheeseProduction(
        string $batchCode,
        string $cheeseType,
        float $weight,
        string $farmName
    ): self {
        return new self(
            type: 'cheese_production',
            title: '🧀 Produksi Keju Baru',
            message: "Batch {$batchCode}\nJenis: {$cheeseType}\nBerat: {$weight} kg",
            farmName: $farmName,
            actionUrl: "/cheese-productions"
        );
    }

    /**
     * Create a payment notification.
     */
    public static function paymentCreated(
        string $periodStart,
        string $periodEnd,
        float $amount,
        string $farmName
    ): self {
        $formattedAmount = number_format($amount, 0, ',', '.');

        return new self(
            type: 'payment',
            title: '💰 Pembayaran Susu',
            message: "Periode: {$periodStart} - {$periodEnd}\nJumlah: Rp {$formattedAmount}",
            farmName: $farmName,
            actionUrl: "/milk-payments"
        );
    }
}
