<?php

/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 10.09.2016
 * Time: 23:30
 */
class SpellobotConfig
{
    const BOT_PATH = '/var/www/spellobot';

    const IMAGE_PATH = '/var/www/spellobot/image';
    const VOICE_PATH = '/var/www/spellobot/voice';

    const IMAGE_EXT = '.jpg';
    const VOICE_EXT = '.opus';

    const WELCOME_IMAGE_FILENAME = '/var/www/spellobot/img/welcome.png';

    const NEG_FACEPALM1_FILENAME = '/var/www/spellobot/img/negative/facepalm1.webp';
    const NEG_FACEPALM2_FILENAME = '/var/www/spellobot/img/negative/facepalm2.webp';
    const NEG_GRUMPY_FILENAME = '/var/www/spellobot/img/negative/grumpy.webp';
    const NEG_TEARS_FILENAME = '/var/www/spellobot/img/negative/tears.webp';
    const NEG_UZBA_FILENAME = '/var/www/spellobot/img/negative/uzba.webp';

    const POS_THUMBS_UP_FILENAME = '/var/www/spellobot/img/positive/gif_thumbsup.gif';
}