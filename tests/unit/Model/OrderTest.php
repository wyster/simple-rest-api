<?php declare(strict_types=1);

namespace Model;

use App\Enum\Status;
use App\Exception\Order\OrderNotCreatedDomainException;
use App\Hydrator\Order;
use App\Model;
use App\Entity;
use App\Hydrator;
use Codeception\Test\Unit;
use UnitTester;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;

class OrderTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function notCreatedExceptionDataProvider(): array
    {
        $resultSetMock = $this->createMock(HydratingResultSet::class);
        $resultSetMock->method('getHydrator')->willReturn(new Hydrator\Order());

        $tableGatewayMock = $this->createMock(TableGateway::class);
        $tableGatewayMock->method('insert')->willReturn(false);
        $tableGatewayMock->method('getResultSetPrototype')->willReturn($resultSetMock);
        $model = new Model\Order($tableGatewayMock);

        $tableGatewayMock2 = $this->createMock(TableGateway::class);
        $tableGatewayMock2->method('insert')->willReturn(true);
        $tableGatewayMock2->method('getResultSetPrototype')->willReturn($resultSetMock);
        $tableGatewayMock2->method('getLastInsertValue')->willReturn('0');
        $model2 = new Model\Order($tableGatewayMock2);
        return [
            [$model],
            [$model2]
        ];
    }

    /**
     * @dataProvider notCreatedExceptionDataProvider
     * @param Model\Order $model
     */
    public function testNotCreatedException(Order $model): void
    {
        $this->expectException(OrderNotCreatedDomainException::class);

        $row = new Entity\Order();
        $row->setUserId(1);
        $row->setStatus(Status::NEW());

        $model->create($row);
    }
}
