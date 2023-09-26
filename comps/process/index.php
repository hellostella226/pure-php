<?php
use Controller\AddressController;
use Controller\MembersController;
use Controller\CodefController;
use Controller\Router;
use Controller\u_*****_uController;

include_once $_SERVER['DOCUMENT_ROOT'] . "/config/config.index.php";

$router = new Router();

//MemberStatus 데이터 변경 API
$router->post('/statusChange', function () {
    $Members = new MembersController();
    $Members->userStatusChange();
});

//Codef API 간편인증요청
$router->post('/codef/auth', function () {
    $codef = new CodefController();
    $codef->sendEasyAuth();
});

//Codef API 건강검진 데이터 요청
$router->post('/codef/nhis', function () {
    $codef = new CodefController();
    $codef->getNHISUserData();
});
//xxxxxx 데이터 생성요청
$router->post('/u_*****_u/report/create', function () {
    $medtek = new u_*****_uController();
    $medtek->createReport();
});

$router->post('/address', function () {
   $address = new AddressController();
   $address->getAddress();
});

$router->run();
