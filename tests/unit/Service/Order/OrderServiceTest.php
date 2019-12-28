<?php declare(strict_types=1);

namespace Model;

use App\Exception\Order\OrderNotFoundDomainException;
use App\Model;
use App\Entity;
use App\Service\Auth\FakeIdentity;
use App\Service\Order\HttpService;
use App\Service\Order\OrderService;
use App\Service\Product\ProductService;
use Codeception\Test\Unit;
use Http\Client\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use ReflectionMethod;
use UnitTester;

class OrderServiceTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function testPayOrderNotFoundException(): void
    {
        $this->expectException(OrderNotFoundDomainException::class);

        $orderPay = new Entity\OrderPay();
        $orderPay->setId(1);
        $modelOrderMock = $this->createMock(Model\Order::class);
        $modelOrderMock->method('getById')->willReturn(null);

        $orderServiceMock = $this->createOrderService($modelOrderMock);

        $orderServiceMock->pay($orderPay);
    }

    public function testIsItPossibleToPayException(): void
    {
        $httpServiceMock = $this->createMock(HttpService::class);
        $exception = new RequestException('', $this->createMock(RequestInterface::class));
        $httpServiceMock->method('checkTsItPossibleToPay')->willThrowException($exception);
        $orderServiceMock = $this->createOrderService(
            null,
            null,
            $httpServiceMock
        );

        $reflectionMethod = new ReflectionMethod($orderServiceMock, 'isItPossibleToPay');
        $reflectionMethod->setAccessible(true);
        $this->assertFalse($reflectionMethod->invoke($orderServiceMock, $httpServiceMock));
    }

    private function createOrderService(
        ?Order $modelOrder = null,
        ?ProductService $productService = null,
        ?HttpService $httpService = null,
        ?ProductOrders $modelProductOrders = null
    ): OrderService {
        return new OrderService(
            $modelOrder ?: $this->createMock(Model\Order::class),
            $productService ?: $this->createMock(ProductService::class),
            $httpService ?: $this->createMock(HttpService::class),
            new FakeIdentity(),
            $modelProductOrders ?: $this->createMock(Model\ProductOrders::class)
        );
    }
}
