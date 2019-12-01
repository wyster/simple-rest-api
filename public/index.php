<?php declare(strict_types=1);

use App\Application;
use App\Helper\Env;

use Dotenv\Dotenv;

define('BASE_DIR', dirname(__DIR__));

require BASE_DIR . '/vendor/autoload.php';

Dotenv::create(BASE_DIR)->load();

error_reporting(E_ALL);
if (Env::isDebug()) {
    error_reporting(-1);
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', 'on');
}

$container = require BASE_DIR . '/config/container.php';

(new Application($container))->run();
