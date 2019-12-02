<?php declare(strict_types=1);

namespace Controller;

use App\Controller\OrderController;
use App\Exception\Order\OrderRequestInvalidDomainException;
use App\Service\Auth\FakeIdentity;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Model\Order;
use Codeception\Test\Unit;
use Exception;
use Faker\Factory as Faker;
use Money\Currency;
use Money\Money;
use UnitTester;
use Zend\Diactoros\ServerRequest;
use Zend\Hydrator\HydratorInterface;
use App\Validator;
use App\Entity;
use App\Model;

class OrderControllerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testOrderNotCreated(): void
    {
        $faker = Faker::create(FAKER_LANG);

        $this->expectException(OrderNotCreatedDomainException::class);
        $this->expectErrorMessage(OrderNotCreatedDomainException::TITLE);

        $modelProduct = $this->tester->grabServiceFromContainer(Model\Product::class);
        $products = [];
        $totalAmount = 0;
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $amount = 1000 + $i;
            $entity->setPrice(new Money($amount, new Currency(getenv('CURRENCY'))));
            if (!$modelProduct->create($entity)) {
                throw new Exception('Row not created');
            }
            $products[$entity->getId()] = $entity;
            $totalAmount += $amount;
        }

        $serverRequest = (new ServerRequest())->withParsedBody(['products' => array_keys($products)]);

        $model = $this->createMock(Order::class);
        $model->expects($this->once())->method('create')->willReturn(false);

        $controller = new OrderController();
        $controller->createAction(
            $serverRequest,
            $this->createMock(HydratorInterface::class),
            $model,
            new FakeIdentity(),
            $this->tester->grabServiceFromContainer(Validator\Order::class)
        );
    }

    public function orderNotValidDataProvider(): array
    {
        return [
            [
                // not found in db
                ['products' => [1, 2, 3]]
            ]
        ];
    }

    /**
     * @dataProvider orderNotValidDataProvider
     * @param array $requestParams
     */
    public function testOrderNotValid(array $requestParams): void
    {
        $this->expectException(OrderRequestInvalidDomainException::class);
        $this->expectErrorMessage(OrderRequestInvalidDomainException::TITLE);

        $serverRequest = (new ServerRequest())->withParsedBody($requestParams);

        $controller = new OrderController();
        $controller->createAction(
            $serverRequest,
            $this->createMock(HydratorInterface::class),
            $this->createMock(Order::class),
            new FakeIdentity(),
            $this->tester->grabServiceFromContainer(Validator\Order::class)
        );
    }
}
