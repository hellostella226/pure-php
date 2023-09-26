<?php
if (isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$apiUrl = "https://v-gc.u_*****_u.com";
if (isDev) {
    $apiUrl = "https://dev.u_*****_u.com";
}

switch (location) {
    case "local" :
        $dbInfo = array(
            'server' => '***.***.**.***',
            'port' => '*****',
            'dbName' => '***',
            'charset' => 'utf8',
            'id' => '****_*****',
            'pw' => '****************'
        );

        $sendtalkApiUrl = "http://lag******com/sendtalk/send";
        break;
    case "develop":
        $dbInfo = array(
            'server' => '*****',
            'port' => '*****',
            'dbName' => '***',
            'charset' => 'utf8',
            'id' => '****_*****',
            'pw' => '****************'
        );

        $sendtalkApiUrl = "http://tag******com/sendtalk/send";
        break;
    default :
        $dbInfo = array(
            'server' => '***.**.**.***',
            'port' => '****',
            'dbName' => '***',
            'charset' => 'utf8',
            'id' => '****_*****',
            'pw' => '*********##'
        );

        $sendtalkApiUrl = "https://ag******com/sendtalk/send";
        break;
}

spl_autoload_register(function ($className) {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/b***-*abc/class/' . $className . '.php')) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/b***-*abc/class/' . $className . '.php';
    }
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/class/" . str_replace('\\', '/', $className) . ".php")) {
        include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/class/" . str_replace('\\', '/', $className) . ".php";
    }
});

$bizmTemplateArray = [
    '***' => [
        'processType' => 10,
        'templateId' => '****04',
        'message' => '[ ]',
];



