<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class OrderPayInvalidAmountDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 403;
    public const TYPE = 'https://example.com/problems/#order-pay-invalid-amount';
    public const TITLE = 'You have invalid amount to complete the transaction.';

    public static function create(int $requestAmount, int $currentAmount): self
    {
        $e = new self(sprintf(
            'In your request order have %s amount, but now it have %s',
            $requestAmount,
            $currentAmount
        ));
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;
        $e->additional = [
            'amount' => $currentAmount
        ];

        return $e;
    }
}
