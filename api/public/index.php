<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

use Config\Database;
use Middlewares\JsonMiddleware;
use Middlewares\CorsMiddleware;
use Components\GenericResponse;

use Controllers\ProductsController;
use Controllers\UserController;
use Controllers\LoginController;

require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$app->setBasePath('/ejercicio-aenima/api/public');
new Database;

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(GenericResponse::obtain(true,"Aplicación Aenima"));
    return $response;
});
$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', UserController::class . ":getAll");     
    $group->get('/{id}', UserController::class . ":getOne");
    
    $group->post('[/]', UserController::class . ":addOne");

});


$app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('[/]', ProductsController::class . ":getAll");     
    $group->get('/{id}', ProductsController::class . ":getOne");

    $group->post('/update-image/{id}', ProductsController::class . ":updateImage");
    $group->post('[/]', ProductsController::class . ":addOne");

    $group->put('/{id}', ProductsController::class . ":updateOne");   
    
    $group->delete('/delete-definitively/{id}', ProductsController::class . ":deleteDefinitively");
    $group->delete('/{id}', ProductsController::class . ":deleteOne");
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('[/]', LoginController::class . ":login");    
});

$app->add(new CorsMiddleware());
$app->add(new JsonMiddleware());
$app->addBodyParsingMiddleware();

$app->run();