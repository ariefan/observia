<?php

$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Replace the webhook method to avoid complex Update object handling
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

            // Handle webhook data manually by directly setting request data
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data && isset($data[\'message\'])) {
                // Set update data on bot and trigger handlers
                $bot->setData($data);
                $bot->fireHandlers();
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
    echo "Pattern not found, creating simpler version...\n";
    
    // Try to find just the run() call and replace it
    $simpleSearch = '$bot->run();';
    $simpleReplace = '// Handle webhook data manually
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data && isset($data["message"])) {
                // Set update data on bot and trigger handlers  
                $bot->setData($data);
                $bot->fireHandlers();
            }';
    
    $finalContent = str_replace($simpleSearch, $simpleReplace, $content);
    
    if ($finalContent !== $content) {
        file_put_contents('app/Http/Controllers/TelegramBotController.php', $finalContent);
        echo "Simple webhook fix applied successfully\n";
    } else {
        echo "Could not apply webhook fix\n";
    }
}
