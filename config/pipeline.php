<?php

declare(strict_types=1);

use Lcobucci\ContentNegotiation\ContentTypeMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipeInterface;

/**
 * Setup middleware pipeline:
 */
return function (MiddlewarePipeInterface $app, ContainerInterface $container): void {
    $app->pipe($container->get(ErrorHandler::class));
    $app->pipe($container->get(Middlewares\FastRoute::class));
    $app->pipe($container->get(ContentTypeMiddleware::class));
    $app->pipe($container->get(App\Middleware\RequestHandler::class));
};
