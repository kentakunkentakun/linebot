<?php
    // Composerでインストールしたライブラリを一括読み込み
    require_once("./phpQuery-onefile.php");
    require_once __DIR__ . '/vendor/autoload.php';

    // アクセストークンを使いCurlHTTPClientをインスタンス化
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));

    //CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

    // LINE Messaging APIがリクエストに付与した署名を取得
    $signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

    //署名をチェックし、正当であればリクエストをパースし配列へ、不正であれば例外処理
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

    //ユーザーが打った単語
    
    foreach ($events as $event) {
        // メッセージを返信
        $replyWord = $event->getText();

        //wikipediaからユーザーが入力した単語の情報を取得
        $wordSource = phpQuery::newDocumentFile('https://dictionary.goo.ne.jp/word/' . $replyWord);
        $h1Text = $wordSource->find('h1')->text();
        $h1Yomi = $wordSource->find('h1')->find('.yomi')->text();
        $detailText = $wordSource->find('.meaning:first')->find('li')->find('p')->text();
        //$textUrl = 'https://dictionary.goo.ne.jp/word/' . $replyWord;

        // ①header、contents内のテキスト要素を作成
        $headerText = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($h1Text);
        $headerText->setWeight('bold');
        $headerText->setWrap(true);

        // ②header用のBoxComponentBuilderを作成
        $headerBox = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', [$headerText]);
        $headerBox->setPaddingBottom("sm");
        $headerBox->setPaddingTop('xl');

        // body
        $bodyText1 = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder($detailText);
        $bodyText1->setWrap(true);
        $bodyText1->setColor("#666566");
        $bodyText1->setSize("sm");

        $bodyBox1 = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', [$bodyText1]);

        $bodyText2 = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder('出典： デジタル大辞泉 (小学館) / goo国語辞書');
        $bodyText2->setColor("#666566");
        $bodyText2->setSize("xxs");
        $bodyText2->setAlign("end");

        $bodyBox2 = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', [$bodyText2]);

        $bodyBox3 = $bodyBox1 = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', [$bodyBox1, $bodyBox2]);
        $bodyBox3->setPaddingAll("xxl");

        //footer
        $footerText = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder("次の文字は「" . substr("abcdef", -2, 1) . "」です。");
        $footerText->setAlign("center");

        $footerBox = new LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder('vertical', [$footerText]);
        $footerBox->setSpacing("sm");
        $footerBox->setFlex(0);
        $footerBox->setPaddingAll("xs");
        $footerBox->setPaddingTop("none");




        // ③BubbleContainerBuilder生成
        $container = new LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder();
        $container->setHeader($headerBox); // ②で作成したheaderを詰める
        $container->setBody($bodyBox3);
        $container->setFooter($footerBox);

        $flexMessage = new LINE\LINEBot\MessageBuilder\FlexMessageBuilder($h1Text, $container);
        //$bot->replyMessage($replyToken, $flexMessage);
        // bodyはsetBody、footerはsetFooterで詰める
        $response = $bot->replyMessage(
            $event->getReplyToken(), $flexMessage
        );
    }
