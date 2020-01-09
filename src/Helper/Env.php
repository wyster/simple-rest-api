<?php declare(strict_types=1);

namespace App\Helper;

final class Env
{
    public const TESTING = 'testing';
    public const PRODUCTION = 'production';

    public static function isTesting(): bool
    {
        return getenv('APP_ENV') === self::TESTING;
    }

    public static function isDebug(): bool
    {
        return (int)getenv('APP_DEBUG') === 1;
    }

    public static function isCli(): bool
    {
        return php_sapi_name() === 'cli';
    }
}
