<?php

$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Add debug logging before the run() call
$search = '// Set explicit webhook mode to avoid auto-detection issues
            $bot->setRunningMode(new Webhook());
            $bot->run();';

$replace = '// Debug: Log incoming request
            $rawInput = $request->getContent();
            Log::info("Webhook received raw input", ["input" => $rawInput]);
            
            try {
                json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
                Log::info("JSON is valid");
            } catch (JsonException $e) {
                Log::error("Invalid JSON received", ["error" => $e->getMessage(), "input" => $rawInput]);
            }
            
            // Set explicit webhook mode to avoid auto-detection issues
            $bot->setRunningMode(new Webhook());
            $bot->run();';

$newContent = str_replace($search, $replace, $content);

file_put_contents('app/Http/Controllers/TelegramBotController.php', $newContent);
echo "Added debug logging to webhook\n";
