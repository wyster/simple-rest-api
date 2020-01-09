<?php declare(strict_types=1);

namespace App\Hydrator;

use App\Entity\ProductOrders as ProductOrdersEntity;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;

final class ProductOrders implements HydratorInterface
{
    /**
     * @var HydratorInterface|null
     */
    protected ?HydratorInterface $hydrator = null;

    public function hydrate(array $data, object $object)
    {
        if (!$object instanceof ProductOrdersEntity) {
            return $object;
        }

        return $this->getHydrator()->hydrate($data, $object);
    }

    public function extract(object $object): array
    {
        if (!$object instanceof ProductOrdersEntity) {
            return [];
        }

        return $this->getHydrator()->extract($object);
    }

    private function getHydrator(): HydratorInterface
    {
        if ($this->hydrator === null) {
            $hydrator = new ClassMethodsHydrator();
            $this->hydrator = $hydrator;
        }

        return $this->hydrator;
    }
}
