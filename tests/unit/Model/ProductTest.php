<?php declare(strict_types=1);

namespace Model;

use App\Model;
use App\Entity;
use Codeception\Test\Unit;
use Exception;
use Faker\Factory as Faker;
use Money\Currency;
use Money\Money;
use UnitTester;

class ProductTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testCalculateTotalAmount(): void
    {
        $faker = Faker::create(FAKER_LANG);

        $model = $this->tester->grabServiceFromContainer(Model\Product::class);
        $rows = [];
        $totalAmount = 0;
        for ($i = 0; $i < 5; $i++) {
            $entity = new Entity\Product();
            $entity->setTitle($faker->text());
            $amount = 1000 + $i;
            $entity->setPrice(new Money($amount, new Currency(getenv('CURRENCY'))));
            if (!$model->create($entity)) {
                throw new Exception('Row not created');
            }
            $rows[$entity->getId()] = $entity;
            $totalAmount += $amount;
        }

        $value = $model->calculateTotalAmount(array_keys($rows));
        $this->assertSame($totalAmount, $value);
    }
}
