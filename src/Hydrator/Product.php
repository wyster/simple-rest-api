<?php declare(strict_types=1);

namespace App\Hydrator;

use App\Entity\Product as ProductEntity;
use Money\Currency;
use Money\Money;
use Zend\Hydrator\ClassMethodsHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Strategy\ClosureStrategy;

class Product implements HydratorInterface
{
    /**
     * @var HydratorInterface|null
     */
    protected ?HydratorInterface $hydrator = null;

    public function hydrate(array $data, object $object)
    {
        if (!$object instanceof ProductEntity) {
            return $object;
        }

        return $this->getHydrator()->hydrate($data, $object);
    }

    public function extract(object $object): array
    {
        if (!$object instanceof ProductEntity) {
            return [];
        }

        return $this->getHydrator()->extract($object);
    }

    private function getHydrator(): HydratorInterface
    {
        if ($this->hydrator === null) {
            $hydrator = new ClassMethodsHydrator();
            $hydrator->addStrategy('price', new ClosureStrategy(
                function (Money $value) {
                    return (int)$value->getAmount();
                },
                function (int $value) {
                    return new Money($value, new Currency(getenv('CURRENCY')));
                }
            ));

            $this->hydrator = $hydrator;
        }

        return $this->hydrator;
    }
}
