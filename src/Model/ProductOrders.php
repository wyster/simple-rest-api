<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\ProductOrders as Entity;
use Exception;

class ProductOrders extends AbstractModel
{
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
