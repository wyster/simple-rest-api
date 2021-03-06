<?php declare(strict_types=1);

namespace App\Hydrator;

use App\Entity\OrderPay as OrderPayEntity;
use Money\Currency;
use Money\Money;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\ClosureStrategy;

final class OrderPay implements HydratorInterface
{
    /**
     * @var HydratorInterface|null
     */
    protected ?HydratorInterface $hydrator = null;

    public function hydrate(array $data, object $object)
    {
        if (!$object instanceof OrderPayEntity) {
            return $object;
        }

        return $this->getHydrator()->hydrate($data, $object);
    }

    public function extract(object $object): array
    {
        if (!$object instanceof OrderPayEntity) {
            return [];
        }

        return $this->getHydrator()->extract($object);
    }

    private function getHydrator(): HydratorInterface
    {
        if ($this->hydrator === null) {
            $hydrator = new ClassMethodsHydrator();
            $hydrator->addStrategy('amount', new ClosureStrategy(
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
