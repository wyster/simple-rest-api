<?php declare(strict_types=1);

use App\Helper\Env;

error_reporting(-1);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

require __DIR__ . '/vendor/autoload.php';
(Dotenv\Dotenv::create(__DIR__))->load();

defined('FAKER_LANG') || define('FAKER_LANG', 'ru_RU');

return [
    'paths' => [
        'migrations' => [
            "App\Db\Migrations" => 'db/migrations'
        ],
        'seeds' => [
            "App\Db\Seeds" => 'db/seeds',
        ]
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => $dbname,
        Env::PRODUCTION => [
            'adapter' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASSWORD'),
            'port' => getenv('DB_PORT'),
            'charset' => 'utf8'
        ],
        Env::TESTING => [
            'adapter' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASSWORD'),
            'port' => getenv('DB_PORT'),
            'charset' => 'utf8'
        ]
    ]
];
