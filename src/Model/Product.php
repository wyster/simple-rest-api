<?php declare(strict_types=1);

namespace App\Model;

use App\Entity\Order as Entity;
use Zend\Db\ResultSet\HydratingResultSet;

class Product extends AbstractModel
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

    /**
     * @return HydratingResultSet
     */
    public function getAll(): iterable
    {
        return $this->getTableGateway()->select();
    }
}
