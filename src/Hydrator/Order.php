<?php declare(strict_types=1);

namespace App\Hydrator;

use App\Entity\Order as OrderEntity;
use Zend\Hydrator\ClassMethodsHydrator;
use Zend\Hydrator\HydratorInterface;

class Order implements HydratorInterface
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function hydrate(array $data, object $object)
    {
        if (!$object instanceof OrderEntity) {
            return $object;
        }

        return $this->getHydrator()->hydrate($data, $object);
    }

    public function extract(object $object): array
    {
        if (!$object instanceof OrderEntity) {
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
