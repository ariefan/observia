<?php

// Read the current file
$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Replace the webhook method
$search = '    public function webhook(Request $request): JsonResponse
    {
        try {
            $bot = $this->telegramService->getBot();
            
            if (!$bot) {
                Log::warning(\'Telegram webhook called but bot not configured\');
                return response()->json([\'ok\' => false, \'error\' => \'Bot not configured\']);
            }

            // Handle the webhook update
            $bot->run();

            return response()->json([\'ok\' => true]);
        } catch (\Exception $e) {
            Log::error(\'Telegram webhook error: \' . $e->getMessage());
            return response()->json([\'ok\' => false, \'error\' => $e->getMessage()]);
        }
    }';

$replace = '    public function webhook(Request $request): JsonResponse
    {
        try {
            $bot = $this->telegramService->getBot();
            
            if (!$bot) {
                Log::warning(\'Telegram webhook called but bot not configured\');
                return response()->json([\'ok\' => false, \'error\' => \'Bot not configured\']);
            }

            // Handle the webhook update manually without auto-detection
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data) {
                $bot->processUpdate($data);
            }

            return response()->json([\'ok\' => true]);
        } catch (\Exception $e) {
            Log::error(\'Telegram webhook error: \' . $e->getMessage());
            return response()->json([\'ok\' => false, \'error\' => $e->getMessage()]);
        }
    }';

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents('app/Http/Controllers/TelegramBotController.php', $newContent);
    echo "Webhook method updated successfully\n";
} else {
    echo "No changes made - pattern not found\n";
}
