<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Order as Entity;

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
            return false;
        }
        $entity->setId($id);

        return true;
    }

    public function update(?Entity $entity): bool
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $this->getTableGateway()->update($data, $entity->getId());

        return true;
    }
}
