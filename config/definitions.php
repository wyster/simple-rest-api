<?php declare(strict_types=1);

use App\Helper\Env;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\TableGateway\TableGateway;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Hydrator\Aggregate\AggregateHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\ProblemDetails\ProblemDetailsResponseFactory;
use Zend\Stratigility\MiddlewarePipe;
use Zend\HttpHandlerRunner\Emitter;
use Zend\Stratigility\MiddlewarePipeInterface;
use App\Model;
use App\Entity;
use App\Service;
use Zend\Db\TableGateway\Feature;
use Zend\Authentication;

return [
    MiddlewarePipeInterface::class => DI\create(MiddlewarePipe::class),
    Emitter\EmitterStack::class => static function (ContainerInterface $c) {
        $stack = new Emitter\EmitterStack();
        $stack->push($c->get(Emitter\SapiEmitter::class));
        return $stack;
    },
    Emitter\EmitterInterface::class => Di\get(Emitter\EmitterStack::class),
    RequestHandlerRunner::class => static function (ContainerInterface $c) {
        return new RequestHandlerRunner(
            $c->get(MiddlewarePipeInterface::class),
            $c->get(Emitter\EmitterInterface::class),
            [ServerRequestFactory::class, 'fromGlobals'],
            static function (Throwable $e) {
                $response = (new Response())
                    ->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)
                    ->withHeader('Content-type', 'application/json');
                return new UnformattedResponse(
                    $response,
                    $e->getMessage()
                );
            }
        );
    },
    Middlewares\FastRoute::class => static function (ContainerInterface $c) {
        $dispatcher = FastRoute\simpleDispatcher(include __DIR__ . '/routes.php');
        return new Middlewares\FastRoute($dispatcher);
    },
    App\Middleware\RequestHandler::class => static function (ContainerInterface $c) {
        return new App\Middleware\RequestHandler($c);
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
        $hydrator->add($c->get(App\Hydrator\ProductOrders::class));

        return $hydrator;
    },
    Model\Product::class => static function (ContainerInterface $c) {
        $rowObjectPrototype = new Entity\Product();
        $resultSetPrototype = new HydratingResultSet();
        $resultSetPrototype->setHydrator($c->get(HydratorInterface::class));
        $resultSetPrototype->setObjectPrototype($rowObjectPrototype);
        $tableGateway = new TableGateway(
            new TableIdentifier('product', 'public'),
            $c->get(DbAdapterInterface::class),
            new Feature\SequenceFeature('id', 'product_id_seq'),
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
            new TableIdentifier('order', 'public'),
            $c->get(DbAdapterInterface::class),
            new Feature\SequenceFeature('id', 'order_id_seq'),
            $resultSetPrototype
        );

        return new Model\Order($tableGateway);
    },
    ProblemDetailsResponseFactory::class => static function () {
        return new ProblemDetailsResponseFactory(
            function () {
                return new Response();
            },
            Env::isDebug(),
            null,
            Env::isDebug()
        );
    },
    Authentication\AuthenticationServiceInterface::class => static function () {
        return new Authentication\AuthenticationService(
            new Authentication\Storage\NonPersistent(),
            new Authentication\Adapter\Callback(function () {
                return new Service\Auth\FakeIdentity();
            })
        );
    },
    Service\Auth\IdentityInterface::class => static function (ContainerInterface $c) {
        return $c->get(Authentication\AuthenticationServiceInterface::class)->getIdentity();
    },
    RequestFactoryInterface::class => Di\get(RequestFactory::class),
    ClientInterface::class => function () {
        return new \Http\Adapter\Guzzle6\Client(new GuzzleHttp\Client());
    },
    Service\Order\HttpService::class => DI\autowire()
        ->constructorParameter('url', getenv('URL_FOR_PAY_POSSIBILITY_CHECK')),
    Model\ProductOrders::class => static function (ContainerInterface $c) {
        $rowObjectPrototype = new Entity\ProductOrders();
        $resultSetPrototype = new HydratingResultSet();
        $resultSetPrototype->setHydrator($c->get(HydratorInterface::class));
        $resultSetPrototype->setObjectPrototype($rowObjectPrototype);

        $tableGateway = new TableGateway(
            new TableIdentifier('product_orders', 'public'),
            $c->get(DbAdapterInterface::class),
            new Feature\SequenceFeature('id', 'product_orders_id_seq'),
            $resultSetPrototype
        );

        return new Model\ProductOrders($tableGateway);
    },
];
