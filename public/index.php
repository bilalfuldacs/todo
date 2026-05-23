<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Bootstrap;
use App\Http\ExceptionHandler;
use App\Http\Request;
use App\Http\Router;
use App\Middleware\CorsMiddleware;

$config = Bootstrap::init();

$request = Request::fromGlobals();
$router = new Router();

$cors = new CorsMiddleware();
$registerRoutes = require dirname(__DIR__) . '/routes/api.php';
$registerRoutes($router);

try {
    $response = $cors->handle($request, static fn (Request $req): \App\Http\Response => $router->dispatch($req));
    $response->send();
} catch (Throwable $e) {
    ExceptionHandler::handle($e);
}
