<?php

// Read the current file
$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Replace the webhook method with proper Update object handling
$search = '            // Handle the webhook update manually without auto-detection
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data) {
                $bot->processUpdate($data);
            }';

$replace = '            // Handle the webhook update properly with Update object
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data) {
                // Create Update object from array
                $update = SergiX44\Nutgram\Telegram\Types\Common\Update::fromArray($data);
                $bot->processUpdate($update);
            }';

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents('app/Http/Controllers/TelegramBotController.php', $newContent);
    echo "Webhook method updated successfully\n";
} else {
    echo "No changes made - pattern not found\n";
}
