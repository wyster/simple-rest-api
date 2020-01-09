<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Order as Entity;
use App\Exception\Order\OrderNotCreatedDomainException;
use Exception;
use Laminas\Db\ResultSet\HydratingResultSet;

class Order extends AbstractModel
{
    /**
     * @param int $id
     * @return null|Entity
     */
    public function getById(int $id): ?Entity
    {
        /**
         * @var HydratingResultSet $resultSet
         */
        $resultSet = $this->getTableGateway()->select(['id' => $id]);
        /**
         * @var object|false $result
         */
        $result = $resultSet->current();
        return $result ?: null;
    }

    public function create(Entity $entity): void
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $added = $this->getTableGateway()->insert($data);

        if (!$added) {
            throw OrderNotCreatedDomainException::create();
        }

        if (!method_exists($this->getTableGateway(), 'getLastInsertValue')) {
            throw new Exception('Method `getLastInsertValue` not implemented');
        }

        $id = (int)$this->getTableGateway()->getLastInsertValue();
        if ($id === 0) {
            throw OrderNotCreatedDomainException::create();
        }
        $entity->setId($id);
    }

    public function update(?Entity $entity): bool
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $this->getTableGateway()->update($data, ['id = ?' => $entity->getId()]);

        return true;
    }
}
