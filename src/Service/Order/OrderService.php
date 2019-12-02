<?php declare(strict_types=1);

namespace App\Service\Order;

use App\Entity;
use App\Enum\Status;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Exception\Order\OrderNotFoundDomainException;
use App\Exception\Order\OrderPayInvalidAmountDomainException;
use App\Exception\Order\OrderPayImPossibleDomainException;
use App\Model;
use App\Service\Auth\IdentityInterface;
use App\Service\Product\ProductService;
use Psr\Http\Client\ClientExceptionInterface;

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
    /**
     * @var IdentityInterface
     */
    private $identity;

    public function __construct(
        Model\Order $modelOrder,
        ProductService $productService,
        HttpService $httpService,
        IdentityInterface $identity
    ) {
        $this->modelOrder = $modelOrder;
        $this->productService = $productService;
        $this->httpService = $httpService;
        $this->identity = $identity;
    }

    private function isItPossibleToPay(): bool
    {
        try {
            return $this->httpService->checkTsItPossibleToPay();
        } catch (ClientExceptionInterface $e) {
        }

        return false;
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

    public function create(Entity\Order $order): void
    {
        $order->setUserId($this->identity->getId());
        $order->setStatus(Status::UNKNOWN());

        if (!$this->modelOrder->create($order)) {
            throw OrderNotCreatedDomainException::create();
        }
    }
}
