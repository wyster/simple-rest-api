<?php declare(strict_types=1);

namespace App\Service\Order;

use App\Entity;
use App\Enum\Status;
use App\Exception\Order\OrderNotFoundDomainException;
use App\Exception\Order\OrderPayInvalidAmountDomainException;
use App\Exception\Order\OrderPayImPossibleDomainException;
use App\Model;
use App\Service\Auth\IdentityInterface;
use App\Service\Product\ProductService;
use Throwable;

final class OrderService
{
    /**
     * @var Model\Order
     */
    private Model\Order $modelOrder;
    /**
     * @var ProductService
     */
    private ProductService $productService;
    /**
     * @var HttpService
     */
    private HttpService $httpService;
    /**
     * @var IdentityInterface
     */
    private IdentityInterface $identity;
    /**
     * @var Model\ProductOrders
     */
    private Model\ProductOrders $modelProductOrders;

    public function __construct(
        Model\Order $modelOrder,
        ProductService $productService,
        HttpService $httpService,
        IdentityInterface $identity,
        Model\ProductOrders $modelProductOrders
    ) {
        $this->modelOrder = $modelOrder;
        $this->productService = $productService;
        $this->httpService = $httpService;
        $this->identity = $identity;
        $this->modelProductOrders = $modelProductOrders;
    }

    private function isItPossibleToPay(): bool
    {
        try {
            $this->httpService->checkTsItPossibleToPay();
        } catch (Throwable $e) {
            return false;
        }

        return true;
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

        $this->modelOrder->create($order);

        foreach ($order->getProducts() as $product) {
            $productOrder = new Entity\ProductOrders();
            $productOrder->setOrder($order->getId());
            $productOrder->setProduct($product);
            // @todo создавать одним запросом
            $this->modelProductOrders->create($productOrder);
        }
    }
}
