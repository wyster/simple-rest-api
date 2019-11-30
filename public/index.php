<?php declare(strict_types=1);

ini_set('display_errors', 'on');
ini_set('error_reporting', -1);

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\MiddlewarePipeInterface;

define('BASE_DIR', dirname(__DIR__));

require BASE_DIR . '/vendor/autoload.php';

Dotenv::create(BASE_DIR)->load();

/**
 * @var ContainerInterface $container
 */
$container = require BASE_DIR . '/config/container.php';

$runner = $container->get(RequestHandlerRunner::class);

$app = $container->get(MiddlewarePipeInterface::class);

$pipeline = require BASE_DIR . '/config/pipeline.php';
$pipeline($app, $container);

$runner->run();
