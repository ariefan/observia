<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use App\Notifications\GeneralTelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TelegramBotController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
        
        // Require super user for test endpoints
        $this->middleware('auth')->except(['webhook']);
        $this->middleware(function ($request, $next) {
            if (!$request->user()->is_super_user) {
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        })->except(['webhook']);
    }

    /**
     * Handle Telegram webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $bot = $this->telegramService->getBot();
            
            if (!$bot) {
                Log::warning('Telegram webhook called but bot not configured');
                return response()->json(['ok' => false, 'error' => 'Bot not configured']);
            }

            // Handle the webhook update
            $bot->run();

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Test bot connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->telegramService->testConnection();
        
        return response()->json($result);
    }

    /**
     * Send test message
     */
    public function sendTestMessage(): JsonResponse
    {
        $result = $this->telegramService->sendTestMessage();
        
        return response()->json($result);
    }

    /**
     * Get bot status
     */
    public function getStatus(): JsonResponse
    {
        $status = $this->telegramService->getStatus();
        
        return response()->json($status);
    }

    /**
     * Get chat info
     */
    public function getChatInfo(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required|string'
        ]);

        $result = $this->telegramService->getChatInfo($request->chat_id);
        
        return response()->json($result);
    }

    /**
     * Send custom notification
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,success,warning,error,health,feeding,breeding,inventory,reminder'
        ]);

        try {
            // Create a dummy notifiable (we'll send to default chat anyway)
            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null; // Use default chat
                }
            };

            Notification::send($notifiable, new GeneralTelegramNotification([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type ?? 'info',
                'created_by' => $request->user()->name,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send custom Telegram notification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh bot configuration
     */
    public function refreshBot(): JsonResponse
    {
        try {
            $this->telegramService->refreshBot();
            
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi bot berhasil dimuat ulang'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ulang konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send health alert test
     */
    public function sendHealthAlertTest(Request $request): JsonResponse
    {
        try {
            $notifiable = new class {
                public function routeNotificationForTelegram() {
                    return null;
                }
            };

            Notification::send($notifiable, new GeneralTelegramNotification([
                'title' => 'Peringatan Kesehatan Ternak',
                'message' => 'Ternak menunjukkan gejala tidak normal dan memerlukan pemeriksaan segera.',
                'type' => 'health',
                'livestock_name' => 'Sapi-001',
                'farm_name' => 'Farm Utama',
                'created_by' => $request->user()->name,
                'action_url' => route('health-records.index')
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Test notifikasi kesehatan berhasil dikirim'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim test notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
