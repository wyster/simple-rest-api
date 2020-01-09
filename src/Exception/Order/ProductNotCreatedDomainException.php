<?php declare(strict_types=1);

namespace App\Exception\Order;

use App\Exception\CommonProblemDetailsExceptionTrait;
use DomainException;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class ProductNotCreatedDomainException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public const STATUS = 500;
    public const TYPE = 'https://example.com/problems/#product-not-created';
    public const TITLE = 'Product not created.';

    public static function create(): self
    {
        $e = new self(self::TITLE);
        $e->status = self::STATUS;
        $e->type = self::TYPE;
        $e->title = self::TITLE;

        return $e;
    }
}
