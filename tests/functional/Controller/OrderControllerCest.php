<?php declare(strict_types=1);

namespace Controller;

use App\Enum\Status;
use App\Exception\Order\OrderPayImPossibleDomainException;
use App\Exception\Order\OrderPayInvalidAmountDomainException;
use App\Exception\Order\OrderRequestInvalidDomainException;
use App\Model;
use App\Entity;
use App\Service\Auth\FakeIdentity;
use App\Service\Auth\IdentityInterface;
use App\Service\Order\HttpService;
use Closure;
use Codeception\Example;
use Codeception\Stub;
use Exception;
use Faker\Factory as Faker;
use Fig\Http\Message\StatusCodeInterface;

use FunctionalTester;
use Money\Currency;
use Money\Money;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;

class OrderControllerCest
{
    public function tryCreate(FunctionalTester $I): void
    {
        $products = $this->createProducts($I);

        $I->haveHttpHeader('Content-type', 'application/json');
        $requestData = [
            'products' => array_keys($products)
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

    public function createFailDataProvider(): array
    {
        return [
            [
                'requestData' => function (FunctionalTester $I) {
                    $products = $this->createProducts($I);
                    $ids = array_keys($products);
                    foreach ($ids as &$item) {
                        $item = (string)$item;
                    }
                    return ['products' => $ids];
                }
            ],
            [
                'requestData' => []
            ],
            [
                'requestData' => ['products' => []]
            ],
        ];
    }

    /**
     * @dataProvider createFailDataProvider
     */
    public function tryCreateFail(FunctionalTester $I, Example $example): void
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $requestData = $example['requestData'];
        if ($requestData instanceof Closure) {
            $requestData = $requestData($I);
        }
        $I->sendPUT('/order', json_encode($requestData));
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $I->canSeeHttpHeader('Content-type', 'application/problem+json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertSame(OrderRequestInvalidDomainException::TITLE, $data['title']);
        $I->assertArrayHasKey('validator', $data);
    }

    public function tryPaySuccess(FunctionalTester $I): void
    {
        $httpServiceMock = Stub::make(HttpService::class, [
            'checkTsItPossibleToPay' => true
        ]);
        $I->addServiceToContainer(HttpService::class, $httpServiceMock);

        $products = $this->createProducts($I);
        $totalAmount = $this->calculateTotalAmount($products);
        $order = $this->createOrder($I, $products);

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

        $updatedOrder = $I->grabServiceFromContainer(Model\Order::class)->getById($order->getId());
        $I->assertTrue($updatedOrder->getStatus()->equals(Status::PAYED()));
    }

    public function payFailDataProvider(): array
    {
        return [
            [
                'requestData' => []
            ],
        ];
    }

    /**
     * @dataProvider payFailDataProvider
     */
    public function tryPayFail(FunctionalTester $I, Example $example): void
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $requestData = $example['requestData'];
        if ($requestData instanceof Closure) {
            $requestData = $requestData($I);
        }
        $I->sendPUT('/order/pay', json_encode($requestData));
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $I->canSeeHttpHeader('Content-type', 'application/problem+json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertSame(OrderRequestInvalidDomainException::TITLE, $data['title']);
        $I->assertArrayHasKey('validator', $data);
    }

    public function tryPayConflict(FunctionalTester $I): void
    {
        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId((new FakeIdentity())->getId());
        $order->setProducts([1, 2, 3]);

        $currentAmount = 0;

        $model = $I->grabServiceFromContainer(Model\Order::class);
        $model->create($order);

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
        $httpServiceMock = Stub::make(HttpService::class, [
            'checkTsItPossibleToPay' => function() {
                throw new Exception('');
            }
        ]);
        $I->addServiceToContainer(HttpService::class, $httpServiceMock);

        $products = $this->createProducts($I);
        $totalAmount = $this->calculateTotalAmount($products);
        $order = $this->createOrder($I, $products);

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

    /**
     * @param FunctionalTester $I
     * @return array
     * @throws Exception
     */
    public function createProducts(FunctionalTester $I): array
    {
        $faker = Faker::create(FAKER_LANG);

        $modelProduct = $I->grabServiceFromContainer(Model\Product::class);
        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $entity->setPrice(new Money(1000, new Currency(getenv('CURRENCY'))));
            $modelProduct->create($entity);
            $products[$entity->getId()] = $entity;
        }

        return $products;
    }

    /**
     * @param Entity\Product[] $products
     * @return int
     */
    private function calculateTotalAmount(array $products): int
    {
        $totalAmount = 0;
        foreach ($products as $product) {
            $totalAmount += (int)$product->getPrice()->getAmount();
        }

        return $totalAmount;
    }

    /**
     * @param FunctionalTester $I
     * @param array $products
     * @return Entity\Order
     * @throws Exception
     */
    private function createOrder(FunctionalTester $I, array $products): Entity\Order
    {
        $order = new Entity\Order();
        $order->setStatus(Status::NEW());
        $order->setUserId((new FakeIdentity())->getId());
        $order->setProducts(array_keys($products));

        $model = $I->grabServiceFromContainer(Model\Order::class);
        $model->create($order);

        return $order;
    }
}
