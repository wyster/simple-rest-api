<?php declare(strict_types=1);

use App\Helper\Env;

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\MiddlewarePipeInterface;

define('BASE_DIR', dirname(__DIR__));

require BASE_DIR . '/vendor/autoload.php';

Dotenv::create(BASE_DIR)->load();

error_reporting(E_ALL);
if (Env::isDebug()) {
    error_reporting(-1);
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', 'on');
}

/**
 * @var ContainerInterface $container
 */
$container = require BASE_DIR . '/config/container.php';

$runner = $container->get(RequestHandlerRunner::class);

$app = $container->get(MiddlewarePipeInterface::class);

$pipeline = require BASE_DIR . '/config/pipeline.php';
$pipeline($app, $container);

$runner->run();
