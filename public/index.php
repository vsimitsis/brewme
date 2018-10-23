<?php

require_once __DIR__ . "/../vendor/autoload.php";

header('Content-Type: application/json');
$router = new Phroute\Phroute\RouteCollector();


// /brew command route
$router->post('/orders', function() {
    return (new BrewMe\Controller\OrderController())->post();
});

// Setup a dispatcher
$dispatcher = new Phroute\Phroute\Dispatcher($router->getData());
try {
    // Try and dispatch the request and catch exceptions from PHRoute
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
} catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    // Route not found
    http_response_code(404);
    exit();
} catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    // Route found, but method not allowed
    http_response_code(405);
    exit();
} catch (Exception $e) {
    // Any other exception
    http_response_code(500);
    exit();
}

print $response;