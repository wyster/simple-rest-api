<?php

declare(strict_types=1);

use FastRoute\RouteCollector;
use App\Controller;

return static function (RouteCollector $r) {
    $r->addRoute(
        'GET',
        '/hello/{name}',
        [Controller\HelloController::class, 'indexAction']
    );
    $r->addRoute(
        'GET',
        '/json/{name}',
        [Controller\HelloController::class, 'jsonAction']
    );
    $r->addRoute(
        'GET',
        '/product',
        [Controller\ProductController::class, 'fetchAllAction']
    );
    $r->addRoute(
        'PUT',
        '/order',
        [Controller\OrderController::class, 'createAction']
    );
    $r->addRoute(
        'PUT',
        '/order/pay',
        [Controller\OrderController::class, 'payAction']
    );
};
