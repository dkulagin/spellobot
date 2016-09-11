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
                    $this->getCurrentWordAndSendToChat(true);
                }
            } else if ($text == "/reset") {
                $this->core->reset();
            } else if ($text == "/test") {
                $this->tgApi->sendSticker(SpellobotConfig::NEG_FACEPALM1_FILENAME);
                $this->tgApi->sendDocument(SpellobotConfig::POS_THUMBS_UP_FILENAME);
            } else if ($text == "/ok") {
                $this->getCurrentWordAndSendToChat(true, $showGroupIntro = false);
            } else if ($text == "/next") {
                $this->getCurrentWordAndSendToChat(false);
            } else {
                $result = $this->core->submitAttempt(strtolower($text));

                if ($result['isMatch']) {
                    if ($result['isGroupComplete']) {
                        $this->sendApprovalAnimation();
                    } else {
                        $this->getCurrentWordAndSendToChat(false);
                    }
                } else {
                    $this->sendDisapprovalSticker();
                    $this->tgApi->sendMessage("): Попробуй ещё раз");
                    $this->getCurrentWordAndSendToChat(false, $showGroupIntro = false, $showShort = true);
                }
            }
        }
    }

    private function getCurrentWordAndSendToChat($isFirstWord, $showGroupIntro = true, $showShort = false)
    {
        $wordArr = $this->core->getNextWord();

        if ($wordArr['isNewGroup'] && $showGroupIntro) {
            $this->compileAndSendGroupDescription($wordArr);
        } else {
            $translation = ' (' . $wordArr['translation'] . ')';
            if (!$isFirstWord && !$showShort) {
                $this->tgApi->sendMessage("Верно! Следующее слово: " . $wordArr['transcription'] . $translation);
            } else {
                $this->tgApi->sendMessage($wordArr['transcription'] . $translation);
            }

            if (!$showShort) {
                $this->tgApi->sendMessage('Как пишется это слово?');
            }

            $voiceFilename = SpellobotConfig::VOICE_PATH . '/' . $wordArr['word'] . SpellobotConfig::VOICE_EXT;
            if (file_exists($voiceFilename)) {
                $this->tgApi->sendVoice($voiceFilename);
            }

            $imageFilename = SpellobotConfig::IMAGE_PATH . '/' . $wordArr['word'] . SpellobotConfig::IMAGE_EXT;
            if (!$showShort && file_exists($imageFilename)) {
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
        на слух. Жми команду /start, если готов начать!");
    }

    private function sendApprovalAnimation()
    {
        $this->tgApi->sendDocument(SpellobotConfig::POS_THUMBS_UP_FILENAME);
        $this->tgApi->sendMessage("Жми /next, чтобы перейти к следующей группе слов.");
    }

    private function sendDisapprovalSticker()
    {
        switch (mt_rand(1, 5)) {
            case 1:
                $this->tgApi->sendSticker(SpellobotConfig::NEG_FACEPALM1_FILENAME);
                break;
            case 2:
                $this->tgApi->sendSticker(SpellobotConfig::NEG_FACEPALM2_FILENAME);
                break;
            case 3:
                $this->tgApi->sendSticker(SpellobotConfig::NEG_GRUMPY_FILENAME);
                break;
            case 4:
                $this->tgApi->sendSticker(SpellobotConfig::NEG_TEARS_FILENAME);
                break;
            case 5:
                $this->tgApi->sendSticker(SpellobotConfig::NEG_UZBA_FILENAME);
                break;
        }
    }
}