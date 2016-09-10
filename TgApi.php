<?php

define('BOT_TOKEN', '239979759:AAHwMmtLvjIIH-D745TA6gZSAC_z3qDNuFo');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 10.09.2016
 * Time: 20:48
 */
class TgApi
{
    private $chatId;

    /**
     * TgApi constructor.
     * @param $chatId
     */
    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function sendMessage($text)
    {
        $this->apiRequest("sendMessage", array('chat_id' => $this->chatId, "text" => $text));
    }

    public function sendVoice($filepath)
    {
        $this->apiRequestPostMultipart("sendVoice", array(
            'chat_id' => $this->chatId,
            "voice" => '@' . $filepath // TODO: rework @ after migration to PHP7
        ), true);
    }

    public function sendImage($filepath)
    {
        $this->apiRequestPostMultipart("sendPhoto", array(
            'chat_id' => $this->chatId,
            "photo" => '@' . $filepath // TODO: rework @ after migration to PHP7
        ), true);
    }

    private function exec_curl_request($handle)
    {
        $response = curl_exec($handle);

        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code >= 500) {
            // do not want to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }

    private function apiRequestPostMultipart($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }

        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = API_URL . $method;

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));

        //  number of POST vars
        curl_setopt($handle, CURLOPT_POST, count($parameters));
        //  POST data
        curl_setopt($handle, CURLOPT_POSTFIELDS, $parameters);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return $this->exec_curl_request($handle);
    }

    private function apiRequest($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }

        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = API_URL . $method . '?' . http_build_query($parameters);

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return $this->exec_curl_request($handle);
    }
}