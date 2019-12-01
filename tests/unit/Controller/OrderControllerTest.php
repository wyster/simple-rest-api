<?php declare(strict_types=1);

namespace Controller;

use App\Controller\OrderController;
use App\Service\Auth\FakeIdentity;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Model\Order;
use Codeception\Test\Unit;
use UnitTester;
use Zend\Diactoros\ServerRequest;
use Zend\Hydrator\HydratorInterface;

class OrderControllerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testOrderNotCreated(): void
    {
        $this->expectException(OrderNotCreatedDomainException::class);
        $this->expectErrorMessage(OrderNotCreatedDomainException::TITLE);

        $serverRequest = (new ServerRequest())->withParsedBody(['products' => [1, 2, 3]]);

        $model = $this->createMock(Order::class);
        $model->expects($this->once())->method('create')->willReturn(false);

        $controller = new OrderController();
        $controller->createAction(
            $serverRequest,
            $this->createMock(HydratorInterface::class),
            $model,
            new FakeIdentity()
        );
    }
}
