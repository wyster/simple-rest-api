<?php declare(strict_types=1);

use App\Application;
use App\Helper\Env;

use Dotenv\Dotenv;

defined('BASE_DIR') || define('BASE_DIR', dirname(__DIR__));
defined('FAKER_LANG') || define('FAKER_LANG', 'ru_RU');

require BASE_DIR . '/vendor/autoload.php';

Dotenv::createImmutable(BASE_DIR)->load();

error_reporting(E_ALL);
if (Env::isDebug()) {
    error_reporting(-1);
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', 'on');
}

$container = require BASE_DIR . '/config/container.php';

return new Application($container);
