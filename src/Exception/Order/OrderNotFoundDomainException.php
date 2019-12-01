<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class OrderNotFoundDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 404;
    public const TYPE = 'https://example.com/problems/#order-not-found';
    public const TITLE = 'Order not found.';

    public static function create(int $orderId): self
    {
        $e = new self(sprintf(
            'Order #%s not found in db',
            $orderId
        ));
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;

        return $e;
    }
}
