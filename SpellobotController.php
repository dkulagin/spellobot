<?php

// TODO: migrate to autoload
include_once 'SpellobotCore.php';
include_once 'TgApi.php';

/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 10.09.2016
 * Time: 21:02
 */
class SpellobotController
{
    private $message;
    private $tgApi;
    private $core;

    /**
     * SpellobotController constructor.
     * @param $tgApi
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function run()
    {
        // process incoming message
        $messageId = $this->message['message_id'];
        $chatId = $this->message['chat']['id'];

        $this->tgApi = new TgApi($chatId);

        if (isset($this->message['text'])) {
            $text = $this->message['text'];

            $this->core = new SpellobotCore($chatId);

            if ($text == "/start") {
                $this->getNewWordAndSendToChat(true);
            } else if ($text == "/test") {
                $this->tgApi->sendMessage('[wɔːl]');
                $this->tgApi->sendVoice('/var/www/spellobot/audio/wall.opus');
                $this->tgApi->sendImage('/var/www/spellobot/image/wall.jpg');
                $this->tgApi->sendMessage('(стена)');
            } else {
                $result = $this->core->submitAttempt($text);

                $this->tgApi->sendMessage("Echo: " . $text);
                $this->tgApi->sendMessage(print_r($result, true));

                if ($result['isMatch']) {
                    $this->getNewWordAndSendToChat(false);
                } else {
                    $this->tgApi->sendMessage("): Попробуй ещё раз");
                }
            }
        }
    }

    private function getNewWordAndSendToChat($isFirstWord)
    {
        $word = $this->core->getNextWord();

        $this->tgApi->sendMessage(($isFirstWord ? "Слово: " : "Верно! Следующее слово: ") . $word);
    }
}