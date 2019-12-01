<?php declare(strict_types=1);

namespace Controller;

use App\Enum\Status;
use App\Model;
use App\Entity;
use Exception;
use Fig\Http\Message\StatusCodeInterface;

use FunctionalTester;

class OrderControllerCest
{
    public function tryCreate(FunctionalTester $I): void
    {
        $I->sendPUT('/order', '{"id":1,"amount":1000}');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->canSeeHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertCount(1, $data);
        $I->assertArrayHasKey('id', $data);
        $I->assertInternalType('int', $data['id']);
        $I->assertTrue($data['id'] > 0, 'Id need be > 0');

        /**
         * @var Model\Order $model
         */
        $model = $I->grabServiceFromContainer(Model\Order::class);
        $order = $model->getById($data['id']);
        $I->assertInstanceOf(Entity\Order::class, $order);
    }

    public function tryPayNotFound(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPut('/order/pay', '{"id":1,"amount":1000}');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
    }

    public function tryPaySuccess(FunctionalTester $I): void
    {
        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId(1);
        $order->setProducts([1, 2, 3]);

        $model = $I->grabServiceFromContainer(Model\Order::class);
        if (!$model->create($order)) {
            throw new Exception('Row not created');
        }

        $I->haveHttpHeader('Content-type', 'application/json');
        $orderPayParams = [
            'id' => $order->getId(),
            'amount' => 1000
        ];
        $I->sendPut('/order/pay', json_encode($orderPayParams));
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->canSeeHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $I->assertSame('[]', $content);
    }
}
