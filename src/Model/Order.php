<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Order as Entity;
use Exception;

class Order extends AbstractModel
{
    /**
     * @param int $id
     * @return null|Entity
     */
    public function getById(int $id): ?Entity
    {
        $result = $this->getTableGateway()->select(['id' => $id])->current();
        return $result ?: null;
    }

    public function create(Entity $entity): bool
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $added = $this->getTableGateway()->insert($data);

        if (!$added) {
            return false;
        }

        $id = (int)$this->getTableGateway()->getLastInsertValue();
        if ($id === 0) {
            throw new Exception('Id need be > 0');
        }
        $entity->setId($id);

        return true;
    }
}
