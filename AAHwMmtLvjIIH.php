<?php

// TODO: migrate to autoload
include_once 'SpellobotController.php';

function processMessage($message)
{
    $controller = new SpellobotController($message);
    $controller->run();
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    // receive wrong update, must not happen
    exit;
}

if (isset($update["message"])) {
    processMessage($update["message"]);
}