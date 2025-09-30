<?php

namespace App\Observers;

use App\Models\LivestockWeight;
use App\Notifications\GeneralTelegramNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class LivestockWeightObserver
{
    /**
     * Handle the LivestockWeight "created" event.
     */
    public function created(LivestockWeight $weight): void
    {
        $this->sendTelegramNotification($weight, 'created');
    }

    /**
     * Handle the LivestockWeight "updated" event.
     */
    public function updated(LivestockWeight $weight): void
    {
        $this->sendTelegramNotification($weight, 'updated');
    }

    /**
     * Handle the LivestockWeight "deleted" event.
     */
    public function deleted(LivestockWeight $weight): void
    {
        $this->sendTelegramNotification($weight, 'deleted');
    }

    /**
     * Send Telegram notification for weight event
     */
    private function sendTelegramNotification(LivestockWeight $weight, string $event): void
    {
        try {
            $weight->load('livestock', 'user', 'audits');

            $livestock = $weight->livestock;
            $user = $weight->user ?? auth()->user();

            if (!$livestock) {
                return;
            }

            $message = $this->formatMessage($weight, $event, $livestock);
            $title = $this->getTitle($event);

            // Get the latest audit for this model
            $audit = $weight->audits()->where('event', $event)->first();
            $actionUrl = $audit ? url("/audits/{$audit->id}") : url("/livestocks/{$livestock->id}");

            // Create a dummy notifiable (will send to default chat)
            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null;
                }
            };

            Notification::send($notifiable, new GeneralTelegramNotification([
                'type' => 'success',
                'title' => $title,
                'message' => $message,
                'livestock_name' => $livestock->name . ' (' . $livestock->aifarm_id . ')',
                'farm_name' => $livestock->farm->name ?? null,
                'created_by' => $user->name ?? 'System',
                'action_url' => $actionUrl,
            ]));

        } catch (\Exception $e) {
            Log::error('Failed to send weight Telegram notification: ' . $e->getMessage(), [
                'weight_id' => $weight->id,
                'event' => $event
            ]);
        }
    }

    private function formatMessage(LivestockWeight $weight, string $event, $livestock): string
    {
        $weightKg = number_format($weight->weight, 1);
        $date = \Carbon\Carbon::parse($weight->date)->format('d/m/Y');

        $messages = [
            'created' => "Pencatatan bobot baru:\n• Bobot: {$weightKg} kg\n• Tanggal: {$date}",
            'updated' => "Data bobot diperbarui:\n• Bobot: {$weightKg} kg\n• Tanggal: {$date}",
            'deleted' => "Data bobot dihapus:\n• Bobot: {$weightKg} kg\n• Tanggal: {$date}",
        ];

        $message = $messages[$event] ?? '';

        // Add weight comparison if this is a new weight
        if ($event === 'created' && isset($livestock->weight) && $livestock->weight > 0) {
            $previousWeight = $livestock->weight;
            $difference = $weight->weight - $previousWeight;
            $diffFormatted = number_format(abs($difference), 1);

            if ($difference > 0) {
                $message .= "\n• Pertambahan: +{$diffFormatted} kg dari {$previousWeight} kg";
            } elseif ($difference < 0) {
                $message .= "\n• Penurunan: -{$diffFormatted} kg dari {$previousWeight} kg";
            }
        }

        return $message;
    }

    private function getTitle(string $event): string
    {
        return match ($event) {
            'created' => 'Bobot Ternak Dicatat',
            'updated' => 'Bobot Ternak Diperbarui',
            'deleted' => 'Bobot Ternak Dihapus',
            default => 'Aktivitas Bobot Ternak',
        };
    }
}