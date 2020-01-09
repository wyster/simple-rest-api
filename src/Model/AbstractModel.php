<?php declare(strict_types=1);

namespace App\Model;

use Exception;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Hydrator\HydratorInterface;

abstract class AbstractModel
{
    /**
     * @var TableGatewayInterface
     */
    private TableGatewayInterface $tableGateway;

    /**
     * @param TableGatewayInterface $tableGateway
     */
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @return HydratorInterface
     */
    protected function getHydrator(): HydratorInterface
    {
        if (!method_exists($this->getTableGateway(), 'getResultSetPrototype')) {
            throw new Exception('Method `getResultSetPrototype` not implemented');
        }

        $resultSet = $this->getTableGateway()->getResultSetPrototype();
        if (!$resultSet instanceof HydratingResultSet) {
            throw new Exception('Support only ' . HydratingResultSet::class);
        }
        return $resultSet->getHydrator();
    }

    /**
     * @return TableGateway|TableGatewayInterface
     */
    protected function getTableGateway(): TableGatewayInterface
    {
        return $this->tableGateway;
    }
}
