<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

use Config\Database;
use Middlewares\JsonMiddleware;
use Components\GenericResponse;

use Controllers\ProductsController;
use Controllers\UsersController;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/ejercicio-aenima/api/public');
new Database;

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(GenericResponse::obtain(true,"Aplicación Aenima"));
    return $response;
});

$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', UsersController::class . ":getAll");     
    $group->get('/{id}', UsersController::class . ":getOne");

    $group->post('[/]', UsersController::class . ":addOne");

    $group->put('/update', UsersController::class . ":updateOne");   
    
    $group->delete('/{id}', UsersController::class . ":deleteOne");
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('[/]', ProductsController::class . ":getAll");     
    $group->get('/{id}', ProductsController::class . ":getOne");

    $group->post('[/]', ProductsController::class . ":addOne");

    $group->put('/update', ProductsController::class . ":updateOne");   
    
    $group->delete('/{id}', ProductsController::class . ":deleteOne");
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('[/]', LoginController::class . ":login");    
});

$app->add(new JsonMiddleware());
$app->addBodyParsingMiddleware();

$app->run();