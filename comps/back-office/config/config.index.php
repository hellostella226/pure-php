<?php
session_start();
$my = [];

global $my;

//도메인 정보에 따른 로컬/개발/운영 분리
switch (substr($_SERVER['SERVER_NAME'], 0, 1)) {
    case "t":
        define('location', 'develop');
        define('isDev', 1);
        define('api', 'http://tag******com');
        define('url', "http://td.g******com");
        define('link', "http://tadmin.g******com");
        break;
    case "l":
        define('location', 'local');
        define('isDev', 1);
        define('api', 'http://lag******com');
        define('url', "http://ld.g******com");
        define('link', "http://ladmin.g******com");
        break;
    case "s":
        define('location', 'service');
        define('isDev', 0);
        define('api', 'https://sag******com');
        define('url', "https://sd.g******com");
        define('link', "https://sadmin.g******com");
        break;
    default:
        define('location', 'service');
        define('isDev', 0);
        define('api', 'https://ag******com');
        define('url', "https://d.g******com");
        define('link', "https://admin.g******com");
        break;
}

$varDir = explode('/', $_SERVER['PHP_SELF'])[1];

require_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/config/config.func.php";

if ($varDir) {
    include_once $_SERVER['DOCUMENT_ROOT'] . "/" . $varDir . "/var.php";
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
