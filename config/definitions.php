<?php declare(strict_types=1);

use App\Helper\Env;
use Lcobucci\ContentNegotiation\ContentTypeMiddleware;
use Lcobucci\ContentNegotiation\Formatter;
use Middlewares\ContentType;
use Psr\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Hydrator\Aggregate\AggregateHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\Stratigility;
use Zend\Stratigility\MiddlewarePipe;
use Zend\HttpHandlerRunner\Emitter;
use Zend\Stratigility\MiddlewarePipeInterface;
use App\Model;
use App\Entity;

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
    },
    DbAdapterInterface::class => static function (ContainerInterface $c) {
        return new Zend\Db\Adapter\Adapter([
            'driver' => 'Pdo_' . getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'port' => getenv('DB_PORT'),
            'charset' => 'utf8'
        ]);
    },
    HydratorInterface::class => static function (ContainerInterface $c) {
        $hydrator = new AggregateHydrator();

        $hydrator->add($c->get(App\Hydrator\Product::class));
        $hydrator->add($c->get(App\Hydrator\Order::class));
        $hydrator->add($c->get(App\Hydrator\OrderPay::class));

        return $hydrator;
    },
    Model\Product::class => static function (ContainerInterface $c) {
        $rowObjectPrototype = new Entity\Product();
        $resultSetPrototype = new HydratingResultSet();
        $resultSetPrototype->setHydrator($c->get(HydratorInterface::class));
        $resultSetPrototype->setObjectPrototype($rowObjectPrototype);
        $tableGateway = new TableGateway(
            'product',
            $c->get(DbAdapterInterface::class),
            null,
            $resultSetPrototype
        );

        return new Model\Product($tableGateway);
    },
    Model\Order::class => static function (ContainerInterface $c) {
        $rowObjectPrototype = new Entity\Order();
        $resultSetPrototype = new HydratingResultSet();
        $resultSetPrototype->setHydrator($c->get(HydratorInterface::class));
        $resultSetPrototype->setObjectPrototype($rowObjectPrototype);
        $tableGateway = new TableGateway(
            'order',
            $c->get(DbAdapterInterface::class),
            null,
            $resultSetPrototype
        );

        return new Model\Order($tableGateway);
    }
];
