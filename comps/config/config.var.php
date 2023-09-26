<?php

//임시 처리
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
        break;
}


spl_autoload_register(function ($className) {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/class/' . $className . '.php')) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/class/' . $className . '.php';
    }
});
spl_autoload_register(function ($className) {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/process/" . str_replace('\\', '/', $className) . ".php")) {
        include_once $_SERVER['DOCUMENT_ROOT'] . "/process/" . str_replace('\\', '/', $className) . ".php";
    }
});

$icoArr = array(
    1 => '카카오톡',
    2 => '페이코',
    3 => '삼성패스',
    4 => '국민은행',
    5 => 'PASS',
    6 => '네이버',
    7 => '신한은행',
    8 => '토스',
);

$device = deviceCheck();

