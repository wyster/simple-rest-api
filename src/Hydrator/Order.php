<?php declare(strict_types=1);

namespace App\Hydrator;

use App\Entity\Order as OrderEntity;
use App\Enum\Status;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\ClosureStrategy;

final class Order implements HydratorInterface
{
    /**
     * @var HydratorInterface|null
     */
    protected ?HydratorInterface $hydrator = null;

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
            $hydrator->addStrategy('status', new ClosureStrategy(
                function (Status $value) {
                    return (int)$value->getValue();
                },
                function (int $value) {
                    return new Status($value);
                }
            ));
            $hydrator->addStrategy('products', new ClosureStrategy(
                function ($value) {
                    return json_encode($value);
                },
                function ($value) {
                    if (is_array($value)) {
                        return $value;
                    }
                    return json_decode($value, true);
                }
            ));

            $this->hydrator = $hydrator;
        }

        return $this->hydrator;
    }
}
