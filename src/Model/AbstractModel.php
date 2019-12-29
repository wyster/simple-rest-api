<?php declare(strict_types=1);

namespace App\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Hydrator\HydratorInterface;

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
        return $this->getTableGateway()->getResultSetPrototype()->getHydrator();
    }

    /**
     * @return TableGateway
     */
    protected function getTableGateway(): TableGatewayInterface
    {
        return $this->tableGateway;
    }
}
