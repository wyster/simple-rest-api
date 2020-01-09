<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

final class ProductOrdersNotCreatedDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 500;
    public const TYPE = 'https://example.com/problems/#product-orders-not-created';
    public const TITLE = 'Order not created.';

    public static function create(): self
    {
        $e = new self(self::TITLE);
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;

        return $e;
    }
}
