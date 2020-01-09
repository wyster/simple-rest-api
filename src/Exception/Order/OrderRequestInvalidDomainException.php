<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

final class OrderRequestInvalidDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 500;
    public const TYPE = 'https://example.com/problems/#order-request-invalid';
    public const TITLE = 'Order request invalid.';

    public static function create(array $additional = []): self
    {
        $e = new self(self::TITLE);
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;
        $e->additional = $additional;

        return $e;
    }
}
