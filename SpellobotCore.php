<?php

// TODO: migrate to autoload
include_once 'RedisCli.php';

/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 10.09.2016
 * Time: 20:08
 */
class SpellobotCore
{
    const DONE_STATUS = '__DONE_STATUS__';

    private $redis;
    private $chatId;

    // TODO: migrate to database
    private $wordGroups = array(
        '-tion' => array(
            'education',
            'vacation',
            'action',
            'station',
            'situation'
        ),
        '-all' => array(
            'call',
            'ball',
            'small',
            array(
                'word' => 'wall',
                'tr' => 'стена',
                'img' => 'стена',
            ),
            'football'
        ),
        '-ight' => array(
            'right',
            'night',
            'fight',
            'light',
            'tight'
        ),
        '-oo-' => array(
            'good',
            'look',
            'school',
            'book',
            'food'
        ),
        '-ive' => array(
            'five',
            'drive',
            'alive'
        ),
        '-ought' => array(
            'thought',
            'brought',
            'bought',
            'fought'
        ),
        '-ther' => array(
            'father',
            'mother',
            'brother'
        ),
        '-ful' => array(
            'beautiful',
            'awful',
            'careful',
            'powerful'
        ),
        '-itch-' => array(
            'itchy',
            'kitchen',
            'switch',
            'witch'
        )
    );

    /**
     * SpellobotCore constructor.
     * @param $chatId
     */
    public function __construct($chatId)
    {
        $this->chatId = $chatId;
        $this->redis = RedisCli::getInstance();
    }

    public function getNextWord()
    {
        foreach ($this->wordGroups as $wordGroupName => $wordGroup) {
            $count = $this->redis->get('chatId_' . $this->chatId . '_group' . $wordGroupName);

            if (!$count) {
                return $this->cacheCurrentWord($wordGroup[0]);
            } else {
                if ($count < count($wordGroup)) {
                    return $this->cacheCurrentWord($wordGroup[$count]);
                }
            }
        }

        return self::DONE_STATUS;
    }

    public function submitAttempt($attempt)
    {
        $word = $this->redis->get('word_' . $this->chatId);

        if ($word != $attempt) {
            return array(
                'isMatch' => false,
                'word' => $word
            );
        } else {
            $this->markWordAsComplete($word);

            return array(
                'isMatch' => true,
                'word' => $word
            );
        }
    }

    private function cacheCurrentWord($word)
    {
        $this->redis->set('word_' . $this->chatId, $word);

        return $word;
    }

    private function markWordAsComplete($completeWord)
    {
        foreach ($this->wordGroups as $wordGroupName => $wordGroup) {
            $count = 0;

            foreach ($wordGroup as $word) {
                if ($word == $completeWord) {
                    $this->redis->set('chatId_' . $this->chatId . '_group' . $wordGroupName, $count + 1);
                }

                $count++;
            }
        }
    }
}