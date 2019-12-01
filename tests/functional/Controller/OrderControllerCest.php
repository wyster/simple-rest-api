<?php declare(strict_types=1);

namespace Controller;

use App\Enum\Status;
use App\Exception\Order\OrderNotFoundDomainException;
use App\Exception\Order\OrderPayImPossibleDomainException;
use App\Exception\Order\OrderPayInvalidAmountDomainException;
use App\Model;
use App\Entity;
use App\Service\Auth\FakeIdentity;
use App\Service\Auth\IdentityInterface;
use App\Service\Order\HttpService;
use Codeception\Stub;
use Exception;
use Faker\Factory as Faker;
use Fig\Http\Message\StatusCodeInterface;

use FunctionalTester;
use Money\Currency;
use Money\Money;
use Zend\ProblemDetails\ProblemDetailsResponseFactory;

class OrderControllerCest
{
    public function tryCreate(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $requestData = [
            'products' => [1, 2, 3]
        ];
        $I->sendPUT('/order', json_encode($requestData));
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->canSeeHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $responseData = json_decode($content, true);
        $I->assertCount(1, $responseData);
        $I->assertArrayHasKey('id', $responseData);
        $I->assertInternalType('int', $responseData['id']);
        $I->assertTrue($responseData['id'] > 0, 'Id need be > 0');

        /**
         * @var Model\Order $model
         */
        $model = $I->grabServiceFromContainer(Model\Order::class);
        $order = $model->getById($responseData['id']);
        $I->assertInstanceOf(Entity\Order::class, $order);
        $I->assertSame(
            $order->getUserId(),
            $I->grabServiceFromContainer(IdentityInterface::class)->getId()
        );
        $I->assertTrue($order->getStatus()->equals(Status::UNKNOWN()));
        $I->assertSame($order->getProducts(), $requestData['products']);
    }

    public function tryPayNotFound(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->sendPut('/order/pay', '{"id":1,"amount":1000}');
        $I->seeResponseCodeIs(OrderNotFoundDomainException::STATUS);
        $I->canSeeHttpHeader('Content-type', ProblemDetailsResponseFactory::CONTENT_TYPE_JSON);
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertSame(OrderNotFoundDomainException::TITLE, $data['title']);
        $I->assertSame(OrderNotFoundDomainException::TYPE, $data['type']);
    }

    public function tryPaySuccess(FunctionalTester $I): void
    {
        $faker = Faker::create(FAKER_LANG);

        $httpServiceMock = Stub::make(HttpService::class, [
            'checkTsItPossibleToPay' => true
        ]);
        $I->addServiceToContainer(HttpService::class, $httpServiceMock);

        $model = $I->grabServiceFromContainer(Model\Product::class);
        $products = [];
        $totalAmount = 0;
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $amount = 1000 + $i;
            $entity->setPrice(new Money($amount, new Currency(getenv('CURRENCY'))));
            if (!$model->create($entity)) {
                throw new Exception('Row not created');
            }
            $products[$entity->getId()] = $entity;
            $totalAmount += $amount;
        }

        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId((new FakeIdentity())->getId());
        $order->setProducts(array_keys($products));

        $model = $I->grabServiceFromContainer(Model\Order::class);
        if (!$model->create($order)) {
            throw new Exception('Row not created');
        }

        $I->haveHttpHeader('Content-type', 'application/json');
        $orderPayParams = [
            'id' => $order->getId(),
            'amount' => $totalAmount
        ];
        $I->sendPut('/order/pay', json_encode($orderPayParams));
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->canSeeHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $I->assertSame('[]', $content);

        $updatedOrder = $model->getById($order->getId());
        $I->assertTrue($updatedOrder->getStatus()->equals(Status::PAYED()));
    }

    public function tryPayConflict(FunctionalTester $I): void
    {
        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId((new FakeIdentity())->getId());
        $order->setProducts([1, 2, 3]);

        $currentAmount = 0;

        $model = $I->grabServiceFromContainer(Model\Order::class);
        if (!$model->create($order)) {
            throw new Exception('Row not created');
        }

        $I->haveHttpHeader('Content-type', 'application/json');
        $requestAmount = 1000;
        $orderPayParams = [
            'id' => $order->getId(),
            'amount' => $requestAmount
        ];
        $I->sendPut('/order/pay', json_encode($orderPayParams));
        $I->seeResponseCodeIs(OrderPayInvalidAmountDomainException::STATUS);
        $I->canSeeHttpHeader('Content-type', ProblemDetailsResponseFactory::CONTENT_TYPE_JSON);
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertSame(OrderPayInvalidAmountDomainException::TITLE, $data['title']);
        $I->assertSame(OrderPayInvalidAmountDomainException::TYPE, $data['type']);
        $I->assertSame(
            sprintf(
                'In your request order have %s amount, but now it have %s',
                $requestAmount,
                $currentAmount
            ),
            $data['detail']
        );
    }

    public function tryPayImpossible(FunctionalTester $I): void
    {
        $faker = Faker::create(FAKER_LANG);

        $httpServiceMock = Stub::make(HttpService::class, [
            'checkTsItPossibleToPay' => false
        ]);
        $I->addServiceToContainer(HttpService::class, $httpServiceMock);

        $model = $I->grabServiceFromContainer(Model\Product::class);
        $products = [];
        $totalAmount = 0;
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $amount = 1000 + $i;
            $entity->setPrice(new Money($amount, new Currency(getenv('CURRENCY'))));
            if (!$model->create($entity)) {
                throw new Exception('Row not created');
            }
            $products[$entity->getId()] = $entity;
            $totalAmount += $amount;
        }

        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId((new FakeIdentity())->getId());
        $order->setProducts(array_keys($products));

        $model = $I->grabServiceFromContainer(Model\Order::class);
        if (!$model->create($order)) {
            throw new Exception('Row not created');
        }

        $I->haveHttpHeader('Content-type', 'application/json');
        $orderPayParams = [
            'id' => $order->getId(),
            'amount' => $totalAmount
        ];
        $I->sendPut('/order/pay', json_encode($orderPayParams));
        $I->seeResponseCodeIs(OrderPayImPossibleDomainException::STATUS);
        $I->canSeeHttpHeader('Content-type', ProblemDetailsResponseFactory::CONTENT_TYPE_JSON);
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertSame(OrderPayImPossibleDomainException::TITLE, $data['title']);
        $I->assertSame(OrderPayImPossibleDomainException::TYPE, $data['type']);
    }
}
