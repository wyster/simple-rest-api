<?php declare(strict_types=1);

namespace App\Service\Order;

use App\Entity;
use App\Enum\Status;
use App\Exception\Order\OrderNotFoundDomainException;
use App\Exception\Order\OrderPayInvalidAmountDomainException;
use App\Exception\Order\OrderPayImPossibleDomainException;
use App\Model;
use App\Service\Product\ProductService;
use App\Service;

class OrderService
{
    /**
     * @var Model\Order
     */
    private $modelOrder;
    /**
     * @var ProductService
     */
    private $productService;
    /**
     * @var HttpService
     */
    private $httpService;

    public function __construct(
        Model\Order $modelOrder,
        Service\Product\ProductService $productService,
        HttpService $httpService
    ) {
        $this->modelOrder = $modelOrder;
        $this->productService = $productService;
        $this->httpService = $httpService;
    }

    public function pay(Entity\OrderPay $orderPay): void
    {
        $order = $this->modelOrder->getById($orderPay->getId());
        if ($order === null) {
            throw OrderNotFoundDomainException::create(
                $orderPay->getId()
            );
        }

        $requestAmount = $orderPay->getAmount();
        $currentAmount = $this->productService->calculateTotalAmountForOrder($order);
        if (!$orderPay->getAmount()->equals($currentAmount)) {
            throw OrderPayInvalidAmountDomainException::create(
                (int)$requestAmount->getAmount(),
                (int)$currentAmount->getAmount()
            );
        }

        if (!$this->isItPossibleToPay()) {
            throw OrderPayImPossibleDomainException::create();
        }

        $order->setStatus(Status::PAYED());

        $this->modelOrder->update($order);
    }

    private function isItPossibleToPay(): bool
    {
        return $this->httpService->checkTsItPossibleToPay();
    }
}
