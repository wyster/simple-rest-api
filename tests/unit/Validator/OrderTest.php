<?php declare(strict_types=1);

namespace Validator;

use Codeception\Test\Unit;
use Faker\Factory as Faker;
use Money\Currency;
use Money\Money;
use UnitTester;
use App\Validator;
use App\Entity;
use App\Model;

final class OrderTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function notValidDataProvider(): array
    {
        return [
            [
                ['products' => 1],
                ['products' => ['Must be array']]
            ],
            [
                ['products' => ['string but not int']],
                ['products' => ['Array must have integer values']]
            ],
            [
                ['products' => [1, 2, 3]],
                ['products' => ['One or more products not found in db']]
            ],
        ];
    }

    /**
     * @dataProvider notValidDataProvider
     * @param array $data
     * @param array $messages
     */
    public function testNotValid(array $data, array $messages): void
    {
        $validator = new Validator\Order($this->createMock(Model\Product::class));
        $this->assertFalse($validator->isValid($data));
        $this->assertSame($messages, $validator->getMessages());
    }

    public function testIsValid(): void
    {
        $faker = Faker::create(FAKER_LANG);

        $modelProduct = $this->tester->grabServiceFromContainer(Model\Product::class);
        $products = [];
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $entity->setPrice(new Money(1000, new Currency(getenv('CURRENCY'))));
            $modelProduct->create($entity);
            $products[$entity->getId()] = $entity;
        }

        $data = ['products' => array_keys($products)];
        $validator = new Validator\Order($modelProduct);
        $this->assertTrue($validator->isValid($data));
    }
}
