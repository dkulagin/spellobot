<?php

// TODO: migrate to autoload
include_once 'SpellobotConfig.php';
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
            } else {
                $result = $this->core->submitAttempt($text);

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
        $wordArr = $this->core->getNextWord();

        $translation = ' (' . $wordArr['translation'] . ')';
        if (!$isFirstWord) {
            $this->tgApi->sendMessage("Верно! Следующее слово: " . $wordArr['transcription'] . $translation);
        } else {
            $this->tgApi->sendMessage($wordArr['transcription'] . $translation);
        }
        $this->tgApi->sendMessage('Как пишется это слово?');

        $voiceFilename = SpellobotConfig::VOICE_PATH . '/' . $wordArr['word'] . SpellobotConfig::VOICE_EXT;
        if (file_exists($voiceFilename)) {
            $this->tgApi->sendVoice($voiceFilename);
        }

        $imageFilename = SpellobotConfig::IMAGE_PATH . '/' . $wordArr['word'] . SpellobotConfig::IMAGE_EXT;
        if (file_exists($imageFilename)) {
            $this->tgApi->sendPhoto($imageFilename);
        }
    }
}