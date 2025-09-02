<?php

$content = file_get_contents('app/Http/Controllers/TelegramBotController.php');

// Add import for Webhook running mode after the last use statement
$lastUse = 'use Illuminate\Support\Facades\Notification;';
$newUse = 'use Illuminate\Support\Facades\Notification;
use SergiX44\Nutgram\RunningMode\Webhook;';

$content = str_replace($lastUse, $newUse, $content);

// Replace just the bot->run() line
$oldRun = '$bot->run();';
$newRun = '// Set explicit webhook mode to avoid auto-detection issues
            $bot->setRunningMode(new Webhook());
            $bot->run();';

$content = str_replace($oldRun, $newRun, $content);

file_put_contents('app/Http/Controllers/TelegramBotController.php', $content);
echo "Applied clean webhook fix\n";
