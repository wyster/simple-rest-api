<?php declare(strict_types=1);

use App\Helper\Env;
use Lcobucci\ContentNegotiation\ContentTypeMiddleware;
use Lcobucci\ContentNegotiation\Formatter;
use Middlewares\ContentType;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility;
use Zend\Stratigility\MiddlewarePipe;
use Zend\HttpHandlerRunner\Emitter;
use Zend\Stratigility\MiddlewarePipeInterface;

return [
    MiddlewarePipeInterface::class => DI\create(MiddlewarePipe::class),
    Emitter\EmitterStack::class => static function (ContainerInterface $c) {
        $stack = new Emitter\EmitterStack();
        $stack->push($c->get(Emitter\SapiEmitter::class));
        return $stack;
    },
    Emitter\EmitterInterface::class => Di\get(Emitter\EmitterStack::class),
    RequestHandlerRunner::class => static function (ContainerInterface $c) {
        $serverRequestFactory = [ServerRequestFactory::class, 'fromGlobals'];
        return new RequestHandlerRunner(
            $c->get(MiddlewarePipeInterface::class),
            $c->get(Emitter\EmitterInterface::class),
            $serverRequestFactory,
            static function (Throwable $e) {
                // @todo handler
            }
        );
    },
    Middlewares\FastRoute::class => static function (ContainerInterface $c) {
        $dispatcher = FastRoute\simpleDispatcher(include __DIR__ . '/routes.php');
        return new Middlewares\FastRoute($dispatcher);
    },
    Middlewares\RequestHandler::class => static function (ContainerInterface $c) {
        return new Middlewares\RequestHandler($c);
    },
    App\Middleware\RequestHandler::class => static function (ContainerInterface $c) {
        return new  App\Middleware\RequestHandler($c);
    },
    Stratigility\Middleware\ErrorHandler::class => static function (ContainerInterface $e) {
        return new Stratigility\Middleware\ErrorHandler(
            static function () {
                return new Response();
            },
            new Stratigility\Middleware\ErrorResponseGenerator(Env::isDebug())
        );
    },
    ContentTypeMiddleware::class => static function (ContainerInterface $e) {
        $contentType = new ContentType([
            'json' => [
                'extension' => ['json'],
                'mime-type' => ['application/json', 'text/json', 'application/x-json'],
                'charset' => true,
            ],
            'html' => [
                'extension' => ['html', 'htm', 'php'],
                'mime-type' => ['text/html', 'application/xhtml+xml'],
                'charset' => true,
            ],
        ]);
        $formatters = [
            'application/json' => new Formatter\Json(),
            'text/html' => new Formatter\StringCast(),
        ];

        return new ContentTypeMiddleware($contentType, $formatters, new StreamFactory());
    }
];
