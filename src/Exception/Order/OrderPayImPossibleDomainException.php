<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class OrderPayImPossibleDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 403;
    public const TYPE = 'https://example.com/problems/#order-pay-not-possible';
    public const TITLE = 'Impossible to pay now.';

    public static function create(): self
    {
        $e = new self(self::TITLE);
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;

        return $e;
    }
}
