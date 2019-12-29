<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\ProductOrders as Entity;
use App\Exception\Order\ProductOrdersNotCreatedDomainException;
use Exception;

class ProductOrders extends AbstractModel
{
    public function create(Entity $entity): void
    {
        $data = $this->getHydrator()->extract($entity);
        unset($data['id']);
        $added = $this->getTableGateway()->insert($data);

        if (!$added) {
            throw ProductOrdersNotCreatedDomainException::create();
        }

        if (!method_exists($this->getTableGateway(), 'getLastInsertValue')) {
            throw new Exception('Method `getLastInsertValue` not implemented');
        }
        $id = (int)$this->getTableGateway()->getLastInsertValue();
        if ($id === 0) {
            throw ProductOrdersNotCreatedDomainException::create();
        }

        $entity->setId($id);
    }
}
