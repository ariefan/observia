<?php

namespace App\Observers;

use App\Models\LivestockMilking;
use App\Notifications\GeneralTelegramNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class LivestockMilkingObserver
{
    /**
     * Handle the LivestockMilking "created" event.
     */
    public function created(LivestockMilking $milking): void
    {
        $this->sendTelegramNotification($milking, 'created');
    }

    /**
     * Handle the LivestockMilking "updated" event.
     */
    public function updated(LivestockMilking $milking): void
    {
        $this->sendTelegramNotification($milking, 'updated');
    }

    /**
     * Handle the LivestockMilking "deleted" event.
     */
    public function deleted(LivestockMilking $milking): void
    {
        $this->sendTelegramNotification($milking, 'deleted');
    }

    /**
     * Send Telegram notification for milking event
     */
    private function sendTelegramNotification(LivestockMilking $milking, string $event): void
    {
        try {
            $milking->load('livestock', 'user');

            $livestock = $milking->livestock;
            $user = $milking->user ?? auth()->user();

            if (!$livestock) {
                return;
            }

            $message = $this->formatMessage($milking, $event);
            $title = $this->getTitle($event);

            // Refresh to get the latest audits created by Auditable trait
            $milking->refresh();

            // Get the latest audit for this model and event
            $audit = $milking->audits()
                ->where('event', $event)
                ->orderBy('created_at', 'desc')
                ->first();

            $actionUrl = $audit ? url("/audits/{$audit->id}") : null;

            // Create a dummy notifiable (will send to default chat)
            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null;
                }
            };

            $notificationData = [
                'type' => 'feeding',
                'title' => $title,
                'message' => $message,
                'livestock_name' => $livestock->name . ' (' . $livestock->aifarm_id . ')',
                'farm_name' => $livestock->farm->name ?? null,
                'created_by' => $user->name ?? 'System',
            ];

            // Only add action_url if audit exists
            if ($actionUrl) {
                $notificationData['action_url'] = $actionUrl;
            }

            Notification::send($notifiable, new GeneralTelegramNotification($notificationData));

        } catch (\Exception $e) {
            Log::error('Failed to send milking Telegram notification: ' . $e->getMessage(), [
                'milking_id' => $milking->id,
                'event' => $event
            ]);
        }
    }

    private function formatMessage(LivestockMilking $milking, string $event): string
    {
        $volume = number_format($milking->milk_volume, 1);
        $date = \Carbon\Carbon::parse($milking->date)->format('d/m/Y');
        $time = $milking->time ? \Carbon\Carbon::parse($milking->time)->format('H:i') : '-';
        $session = $milking->session ?? '-';

        $messages = [
            'created' => "Pencatatan produksi susu baru:\n• Volume: {$volume} liter\n• Tanggal: {$date}\n• Waktu: {$time}\n• Sesi: {$session}",
            'updated' => "Data produksi susu diperbarui:\n• Volume: {$volume} liter\n• Tanggal: {$date}\n• Waktu: {$time}\n• Sesi: {$session}",
            'deleted' => "Data produksi susu dihapus:\n• Volume: {$volume} liter\n• Tanggal: {$date}",
        ];

        $message = $messages[$event] ?? '';

        if ($milking->notes) {
            $message .= "\n• Catatan: {$milking->notes}";
        }

        return $message;
    }

    private function getTitle(string $event): string
    {
        return match ($event) {
            'created' => 'Produksi Susu Dicatat',
            'updated' => 'Produksi Susu Diperbarui',
            'deleted' => 'Produksi Susu Dihapus',
            default => 'Aktivitas Produksi Susu',
        };
    }
}