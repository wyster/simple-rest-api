<?php

declare(strict_types=1);

use App\Helper\Env;
use FastRoute\RouteCollector;
use App\Controller;
use Fig\Http\Message\StatusCodeInterface;

return static function (RouteCollector $r) {
    $r->addRoute(
        'GET',
        '/',
        function () {
            return new Laminas\Diactoros\Response\TextResponse('Hello world!');
        }
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

    if (Env::isTesting()) {
        $r->addRoute(['GET', 'POST'], '/c3/{name:.+}', function () {
            return (new Laminas\Diactoros\Response())->withStatus(StatusCodeInterface::STATUS_OK);
        });
    }
};
