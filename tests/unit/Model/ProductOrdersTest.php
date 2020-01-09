<?php declare(strict_types=1);

namespace Model;

use App\Exception\Order\ProductOrdersNotCreatedDomainException;
use App\Model;
use App\Entity;
use App\Hydrator;
use Codeception\Test\Unit;
use UnitTester;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;

final class ProductOrdersTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function notCreatedExceptionDataProvider(): array
    {
        $resultSetMock = $this->createMock(HydratingResultSet::class);
        $resultSetMock->method('getHydrator')->willReturn(new Hydrator\ProductOrders());

        $tableGatewayMock = $this->createMock(TableGateway::class);
        $tableGatewayMock->method('insert')->willReturn(false);
        $tableGatewayMock->method('getResultSetPrototype')->willReturn($resultSetMock);
        $model = new Model\ProductOrders($tableGatewayMock);

        $tableGatewayMock2 = $this->createMock(TableGateway::class);
        $tableGatewayMock2->method('insert')->willReturn(true);
        $tableGatewayMock2->method('getResultSetPrototype')->willReturn($resultSetMock);
        $tableGatewayMock2->method('getLastInsertValue')->willReturn('0');
        $model2 = new Model\ProductOrders($tableGatewayMock2);
        return [
            [$model],
            [$model2]
        ];
    }

    /**
     * @dataProvider notCreatedExceptionDataProvider
     * @param Model\ProductOrders $model
     */
    public function testNotCreatedException(Model\ProductOrders $model): void
    {
        $this->expectException(ProductOrdersNotCreatedDomainException::class);

        $row = new Entity\ProductOrders();
        $row->setProduct(1);
        $row->setOrder(1);

        $model->create($row);
    }
}
