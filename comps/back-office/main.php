<?php
// 테스트용

include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/config/config.index.php";

$ipaddress = getClientIp();

$service = (isset($_GET['service']) && $_GET['service'] !== '') ? $_GET['service'] : 'offer***';
$destination = (isset($_GET['dest']) && $_GET['dest'] !== '') ? $_GET['dest'] : 'goods';

$request = $_REQUEST;
if(isset($_FILES)) {
    $request[] = $_FILES;
}

$router = new \Router();
$router->mount('/' . $service . '/' . $destination, function () use ($router, $service, $destination, $request) {
    $method = $router->getRequestMethod();
    $router->all('/', function () use ($method, $service, $destination, $request) {
        $controllerName = 'Controller\\' . ucfirst($service) . 'Controller';
        $$service = new $controllerName($service);
        switch ($method) {
            case 'POST' :
                $$service->request($request);
                break;
            case 'GET':
                $$service->search($destination, $request);
                break;
            default :
                $$service->definedDestination($destination, $view);
                break;
        }
    });

});
$router->run();
