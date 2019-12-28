<?php declare(strict_types=1);

namespace Model;

use App\Exception\Order\ProductNotCreatedDomainException;
use App\Model;
use App\Entity;
use Codeception\Test\Unit;
use Faker\Factory as Faker;
use Money\Currency;
use Money\Money;
use UnitTester;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use App\Hydrator;

class ProductTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

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
            $model->create($entity);
            $rows[$entity->getId()] = $entity;
            $totalAmount += $amount;
        }

        $value = $model->calculateTotalAmount(array_keys($rows));
        $this->assertSame($totalAmount, $value);
    }

    public function notCreatedExceptionDataProvider(): array
    {
        $resultSetMock = $this->createMock(HydratingResultSet::class);
        $resultSetMock->method('getHydrator')->willReturn(new Hydrator\Product());

        $tableGatewayMock = $this->createMock(TableGateway::class);
        $tableGatewayMock->method('insert')->willReturn(false);
        $tableGatewayMock->method('getResultSetPrototype')->willReturn($resultSetMock);
        $model = new Model\Product($tableGatewayMock);

        $tableGatewayMock2 = $this->createMock(TableGateway::class);
        $tableGatewayMock2->method('insert')->willReturn(true);
        $tableGatewayMock2->method('getResultSetPrototype')->willReturn($resultSetMock);
        $tableGatewayMock2->method('getLastInsertValue')->willReturn('0');
        $model2 = new Model\Product($tableGatewayMock2);
        return [
            [$model],
            [$model2]
        ];
    }

    /**
     * @dataProvider notCreatedExceptionDataProvider
     * @param Model\Product $model
     */
    public function testNotCreatedException(Product $model): void
    {
        $this->expectException(ProductNotCreatedDomainException::class);

        $row = new Entity\Product();
        $row->setPrice(Money::USD(100));
        $row->setTitle('test');

        $model->create($row);
    }
}
