<?php
if (!defined('INIT')) {
    define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
    include(ROOT . DIRECTORY_SEPARATOR . "init.php");
}

use Alexartwww\Betoola\Controllers\ShopController;
use Alexartwww\Betoola\Services\DBService;
use Alexartwww\Betoola\Services\ShopService;
use Alexartwww\Betoola\Handlers\HttpErrorHandler;
use Alexartwww\Betoola\Handlers\ShutdownHandler;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

$displayErrorDetails = true;

$container = new Container;
AppFactory::setContainer($container);
$app = AppFactory::create();

$container = $app->getContainer();
$container->set('DBService', function () {
    $host = getenv("MYSQL_HOST");
    $db = getenv("MYSQL_DB");
    $user = getenv("MYSQL_USER");
    $password = getenv("MYSQL_PASSWORD");

    $dbService = new DBService($host, $db, $user, $password);
    return $dbService;
});
$container->set('ShopService', function ($container) {
    $dbService = $container->get('DBService');
    $shopService = new ShopService($dbService);
    return $shopService;
});
$container->set('ShopController', function ($container) {
    $shopService = $container->get('ShopService');
    $dbService = $container->get('DBService');
    $controller = new ShopController($shopService, $dbService);
    return $controller;
});


$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);


$app->get('/', function ($req, $res, $args) {
    return $this->get('ShopController')->home($req, $res, $args);
});

$app->get('/api/categories', function ($req, $res, $args) {
    return $this->get('ShopController')->getCategories($req, $res, $args);
});

$app->get('/api/products', function ($req, $res, $args) {
    return $this->get('ShopController')->getProducts($req, $res, $args);
});

$app->get('/api/prices', function ($req, $res, $args) {
    return $this->get('ShopController')->getPrices($req, $res, $args);
});

$app->post('/api/prices', function ($req, $res, $args) {
    return $this->get('ShopController')->postPrice($req, $res, $args);
});

$app->run();
