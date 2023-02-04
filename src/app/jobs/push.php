<?php

require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');
require_once dirname(__DIR__, 2) . "/app/includes/parse.php";

$dotenv      = initDotenv();
$db          = initDb();

function sendMessage($text) {
    $apiToken = getenv('TELEGRAM_BOT_API');
    $data = [
        'chat_id' => getenv('TELEGRAM_CHAT_ID'),
        'text' => $text
    ];
    $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data));
    return  json_decode($response, true);
}

sendMessage('test');

