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
            array(
                'word' => 'education',
                'translation' => 'образование',
                'transcription' => '[ˌɛdʒʊˈkeɪʃn]',
            ),
            array(
                'word' => 'vacation',
                'translation' => 'отпуск',
                'transcription' => '[vəˈkeɪʃ(ə)n]',
            ),
            array(
                'word' => 'action',
                'translation' => 'действие',
                'transcription' => '[ˈæk.ʃən]',
            ),
            array(
                'word' => 'station',
                'translation' => 'станция',
                'transcription' => '[ˈsteɪʃən]',
            ),
            array(
                'word' => 'conversation',
                'translation' => 'разговор, беседа',
                'transcription' => '[ˌkɒnvəˈseɪʃən]',
            ),
        ),
        '-all' => array(
            array(
                'word' => 'call',
                'translation' => 'звонок, звонить',
                'transcription' => '[сɔːl]',
            ),
            array(
                'word' => 'small',
                'translation' => 'маленький',
                'transcription' => '[ˈsmɔːl]',
            ),
            array(
                'word' => 'ball',
                'translation' => 'мяч',
                'transcription' => '[bɔːl]',
            ),
            array(
                'word' => 'wall',
                'translation' => 'стена',
                'transcription' => '[wɔːl]',
            ),
            array(
                'word' => 'football',
                'translation' => 'футбол',
                'transcription' => '[ˈfu̇t-ˌbɔːl]',
            ),
        ),
        '-ight' => array(
            array(
                'word' => 'right',
                'translation' => 'направо',
                'transcription' => '[ɹaɪt]',
            ),
            array(
                'word' => 'night',
                'translation' => 'ночь',
                'transcription' => '[naɪt]',
            ),
            array(
                'word' => 'fight',
                'translation' => 'бой, драка, драться',
                'transcription' => '[faɪt]',
            ),
            array(
                'word' => 'flight',
                'translation' => 'полёт, перелёт, совершать перелёт',
                'transcription' => '[flaɪt]',
            ),
            array(
                'word' => 'light',
                'translation' => 'свет',
                'transcription' => '[laɪt]',
            ),
        ),
        '-oo-' => array(
            array(
                'word' => 'good',
                'translation' => 'хороший',
                'transcription' => '[gud]',
            ), array(
                'word' => 'look',
                'translation' => 'взгляд, смотреть',
                'transcription' => '[luk]',
            ), array(
                'word' => 'school',
                'translation' => 'школа',
                'transcription' => '[skuːl]',
            ), array(
                'word' => 'book',
                'translation' => 'книга',
                'transcription' => '[buk]',
            ), array(
                'word' => 'food',
                'translation' => 'еда, пища',
                'transcription' => '[fuːd]',
            )
        ),
        '-ive' => array(
            array(
                'word' => 'five',
                'translation' => 'пять',
                'transcription' => '[faɪv]',
            ), array(
                'word' => 'drive',
                'translation' => 'ехать на машине, управлять автомобилем',
                'transcription' => '[draɪv]',
            ), array(
                'word' => 'alive',
                'translation' => 'живой',
                'transcription' => '[ə\'laɪv]',
            )
        ),
        '-ought' => array(
            array(
                'word' => 'thought',
                'translation' => 'мысль, думал (непр. от think)',
                'transcription' => '[θɔːt]',
            ), array(
                'word' => 'brought',
                'translation' => 'принёс (непр. от bring)',
                'transcription' => '[brɔːt]',
            ), array(
                'word' => 'bought',
                'translation' => 'купил (непр. от buy)',
                'transcription' => '[bɔːt]',
            ), array(
                'word' => 'fought',
                'translation' => 'дрался, бился (непр. от fight)',
                'transcription' => '[fɔːt]',
            )
        ),
        '-ther' => array(
            array(
                'word' => 'father',
                'translation' => 'папа, отец',
                'transcription' => '[ˈfɑːðəʳ]',
            ), array(
                'word' => 'mother',
                'translation' => 'мама, мать',
                'transcription' => '[ˈmʌðəʳ]',
            ), array(
                'word' => 'brother',
                'translation' => 'брат',
                'transcription' => '[\'brʌðəʳ]',
            )
        ),
        '-ful' => array(
            array(
                'word' => 'beautiful',
                'translation' => 'красивый, привлекательный',
                'transcription' => '[\'bjuːtəf(ə)l]',
            ), array(
                'word' => 'awful',
                'translation' => 'отвратительный',
                'transcription' => '[\'ɔːf(ə)l]',
            ), array(
                'word' => 'careful',
                'translation' => 'внимательный, осторожный',
                'transcription' => '[\'keəf(ə)l]',
            ), array(
                'word' => 'powerful',
                'translation' => 'мощный, могущественный',
                'transcription' => '[\'pauəf(ə)l]',
            )
        ),
        '-itch-' => array(
            array(
                'word' => 'pitch',
                'translation' => 'питч, презентация',
                'transcription' => '[pɪʧ]',
            ), array(
                'word' => 'kitchen',
                'translation' => 'кухня',
                'transcription' => '[\'kɪʧɪn]',
            ), array(
                'word' => 'switch',
                'translation' => 'выключатель, включать/выключать',
                'transcription' => '[swɪʧ]',
            ), array(
                'word' => 'witch',
                'translation' => 'ведьма, колдунья',
                'transcription' => '[\'bjuːtəf(ə)l]',
            )
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

        if ($word) {
            $word = json_decode($word, true);
        }

        if ($word['word'] != $attempt) {
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

    private function cacheCurrentWord($wordArr)
    {
        $this->redis->set('word_' . $this->chatId, json_encode($wordArr));

        return $wordArr;
    }

    private function markWordAsComplete($completeWordArr)
    {
        foreach ($this->wordGroups as $wordGroupName => $wordGroup) {
            $count = 0;

            foreach ($wordGroup as $wordArr) {
                if ($wordArr == $completeWordArr) {
                    $this->redis->set('chatId_' . $this->chatId . '_group' . $wordGroupName, $count + 1);

                    return;
                }

                $count++;
            }
        }
    }
}