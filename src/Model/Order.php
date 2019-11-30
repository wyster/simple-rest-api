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

    public function create(Entity $order): bool
    {
        $order->setId(1);
        return true;
    }
}
