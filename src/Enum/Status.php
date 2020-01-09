<?php declare(strict_types=1);

namespace App\Enum;

use MyCLabs\Enum\Enum;

final class Status extends Enum
{
    private const UNKNOWN = 0;
    private const NEW = 1;
    private const PAYED = 2;

    /**
     * @return self
     */
    public static function UNKNOWN(): self
    {
        return new self(self::UNKNOWN);
    }

    /**
     * @return self
     */
    public static function NEW(): self
    {
        return new self(self::NEW);
    }

    /**
     * @return self
     */
    public static function PAYED(): self
    {
        return new self(self::PAYED);
    }
}
