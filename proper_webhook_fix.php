<?php

$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Add the import for Webhook running mode
$imports = 'use Illuminate\Support\Facades\Notification;';
$newImports = 'use Illuminate\Support\Facades\Notification;
use SergiX44\Nutgram\RunningMode\Webhook;';

$content = str_replace($imports, $newImports, $content);

// Replace the webhook method with explicit running mode setting
$search = '// Handle webhook data manually
            $json = $request->getContent();
            $data = json_decode($json, true);
            
            if ($data && isset($data["message"])) {
                // Set update data on bot and trigger handlers  
                $bot->setData($data);
                $bot->fireHandlers();
            }';

$replace = '// Set webhook running mode explicitly and process
            $bot->setRunningMode(new Webhook());
            $bot->run();';

$newContent = str_replace($search, $replace, $content);

file_put_contents('app/Http/Controllers/TelegramBotController.php', $newContent);
echo "Applied proper webhook fix with explicit running mode\n";
