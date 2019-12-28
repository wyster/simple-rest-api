<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Product as Entity;
use App\Exception\Order\ProductNotCreatedDomainException;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Product extends AbstractModel
{
    /**
     * @return HydratingResultSet
     */
    public function getAll(): iterable
    {
        return $this->getTableGateway()->select();
    }

    public function create(Entity $entity): void
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $added = $this->getTableGateway()->insert($data);

        if (!$added) {
            throw ProductNotCreatedDomainException::create();
        }

        $id = (int)$this->getTableGateway()->getLastInsertValue();
        if ($id === 0) {
            throw ProductNotCreatedDomainException::create();
        }

        $entity->setId($id);
    }

    public function calculateTotalAmount(array $ids): ?int
    {
        $adapter = $this->getTableGateway()->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select($this->getTableGateway()->getTable())
            ->columns(['amount' => new Expression('SUM(price)')])
            ->where(['id' => $ids]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current()['amount'];
    }

    public function isAllIdsExists(array $ids): bool
    {
        $adapter = $this->getTableGateway()->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select($this->getTableGateway()->getTable())
            ->columns(['count' => new Expression('COUNT(id)')])
            ->where(['id' => $ids]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current()['count'] === count($ids);
    }
}
