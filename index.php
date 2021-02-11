<?php
    // Composerでインストールしたライブラリを一括読み込み
    require_once __DIR__ . '/vendor/autoload.php';

    // アクセストークンを使いCurlHTTPClientをインスタンス化
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('3ppOuoOzRoKr1UJ9Zp+KqowYa/b5TX/kz5p4KNzSu/Nss3Lv8EZusdwQ0IPRo2oMCv7ixwk1W9FtjDPoD2xYdvFlr/AeiRE17dIOXPIyKMH6vQvTUsMzJtrdSbVzTWVVhPK2JUU8ZuhDq2CXRjuA9AdB04t89/1O/w1cDnyilFU=');

    //CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'd01895a03bb0f12f54d79a9a10d08f54']);

    // LINE Messaging APIがリクエストに付与した署名を取得
    $signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

    //署名をチェックし、正当であればリクエストをパースし配列へ、不正であれば例外処理
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

    foreach ($events as $event) {
        // メッセージを返信
        $response = $bot->replyMessage(
            $event->getReplyToken(), new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("かいそ")  
        );
    }
