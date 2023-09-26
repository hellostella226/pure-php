<?php
session_start();
$my = [];

global $my;

//도메인 정보에 따른 로컬/개발/운영 분리
switch (substr($_SERVER['SERVER_NAME'], 0, 1)) {
    case "t":
        define('location', 'develop');
        define('isDev', 1);
        define('api', 'http://tag질환*com');
        break;
    case "l":
        define('location', 'local');
        define('isDev', 1);
        define('api', 'http://lag******com');
        break;
    case "s":
        define('location', 'service');
        define('isDev', 0);
        define('api', 'https://sag******com');
        break;
    default:
        define('location', 'service');
        define('isDev', 0);
        define('api', 'https://ag******com');
        break;
}

$varDir = explode('/', $_SERVER['PHP_SELF'])[1];


require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.func.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.var.php";
