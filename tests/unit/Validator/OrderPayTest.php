<?php declare(strict_types=1);

namespace Validator;

use App\Model\Order;
use App\Service\Auth\IdentityInterface;
use Codeception\Test\Unit;
use UnitTester;
use App\Validator;
use App\Entity;
use App\Model;

class OrderPayTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function notValidDataProvider(): array
    {
        $order = new Entity\Order();
        $order->setUserId(1);
        return [
            [
                [],
                ['id' => ['Is required']]
            ],
            [
                ['id' => 'string but not int'],
                ['id' => ['Must be integer']]
            ],
            [
                ['id' => 1],
                ['id' => ['Order not found in db or access denied']]
            ],
            [
                ['id' => 1],
                ['amount' => ['Is required']],
                $order
            ],
            [
                ['id' => 1, 'amount' => '10$'],
                ['amount' => ['Must be integer']],
                $order
            ],
            [
                ['id' => 1],
                ['id' => ['Order not found in db or access denied']],
                $order,
                2
            ],
        ];
    }

    /**
     * @dataProvider notValidDataProvider
     * @param array $data
     * @param array $messages
     * @param null $order
     * @param int $identity
     */
    public function testNotValid(array $data, array $messages, $order = null, int $identity = 1): void
    {
        $orderMock = $this->createMock(Model\Order::class);
        $orderMock->method('getById')->willReturn($order);

        $identityMock = $this->createMock(IdentityInterface::class);
        $identityMock->method('getId')->willReturn($identity);

        $validator = new Validator\OrderPay($orderMock, $identityMock);
        $this->assertFalse($validator->isValid($data));
        $this->assertSame($messages, $validator->getMessages());
    }

    public function testIsValid(): void
    {
        $order = new Entity\Order();
        $order->setUserId(1);
        $data = ['id' => 1, 'amount' => 10];

        $orderMock = $this->createMock(Model\Order::class);
        $orderMock->method('getById')->willReturn($order);

        $identityMock = $this->createMock(IdentityInterface::class);
        $identityMock->method('getId')->willReturn($order->getUserId());

        $validator = new Validator\OrderPay($orderMock, $identityMock);
        $this->assertTrue($validator->isValid($data));
    }
}
