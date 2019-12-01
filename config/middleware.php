<?php declare(strict_types=1);

namespace App;

use App\Middleware\IdentityHandler;
use App\Middleware\RequestHandler;
use Middlewares\FastRoute;
use Middlewares\JsonPayload;
use Zend\ProblemDetails\ProblemDetailsMiddleware;
use Zend\ProblemDetails\ProblemDetailsNotFoundHandler;

return [
    ProblemDetailsMiddleware::class,
    IdentityHandler::class,
    FastRoute::class,
    JsonPayload::class,
    RequestHandler::class,
    ProblemDetailsNotFoundHandler::class
];
