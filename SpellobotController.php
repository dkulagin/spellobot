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
                if ($this->core->isNewUser()) {
                    $this->showWelcomeMessage();

                    $this->core->setUserFlag();
                } else {
                    $this->getNewWordAndSendToChat(true);
                }
            } else if ($text == "/reset") {
                $this->core->reset();
            } else if ($text == "/ok") {
                $this->getNewWordAndSendToChat(true, $showGroupIntro = false);
            } else {
                $result = $this->core->submitAttempt(strtolower($text));

                if ($result['isMatch']) {
                    $this->getNewWordAndSendToChat(false);
                } else {
                    $this->tgApi->sendMessage("): Попробуй ещё раз");
                }
            }
        }
    }

    private function getNewWordAndSendToChat($isFirstWord, $showGroupIntro = true)
    {
        $wordArr = $this->core->getNextWord();

        if ($wordArr['isNewGroup'] && $showGroupIntro) {
            $this->compileAndSendGroupDescription($wordArr);
        } else {
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

    private function compileAndSendGroupDescription($wordArr)
    {
        $this->tgApi->sendMessage($wordArr['groupMeta']['description'] . ". Например: ");

        foreach ($wordArr['group'] as $word) {
            $this->tgApi->sendMessage($word['word'] . " " . $word['transcription']
                . ' (' . $word['translation'] . ')');
        }

        $this->tgApi->sendMessage("Набирай команду /ok, чтобы начать тренировку");
    }

    private function showWelcomeMessage()
    {
        $this->tgApi->sendPhoto(SpellobotConfig::WELCOME_IMAGE_FILENAME);

        $this->tgApi->sendMessage("Привет! Меня зовут Spellobot и я помогу тебе быстро научиться записывать английские слова 
        на слух. Жми комманду /start, если готов начать!");
    }
}