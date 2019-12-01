<?php declare(strict_types=1);

namespace App\Service\Product;

use App\Entity;
use App\Model;
use Money\Currency;
use Money\Money;

class ProductService
{
    /**
     * @var Model\Product
     */
    private $modelProduct;

    public function __construct(Model\Product $product)
    {
        $this->modelProduct = $product;
    }

    /**
     * @return Model\Product
     */
    private function getModelProduct(): Model\Product
    {
        return $this->modelProduct;
    }

    public function calculateTotalAmountForOrder(Entity\Order $order): Money
    {
        return new Money(
            $this->getModelProduct()->calculateTotalAmount($order->getProducts()),
            new Currency(getenv('CURRENCY'))
        );
    }
}
