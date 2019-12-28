<?php declare(strict_types=1);

namespace App\Entity;

class ProductOrders extends AbstractEntity
{
    /**
     * @var null|int
     */
    private ?int $id;

    /**
     * @var int
     */
    private int $product;

    /**
     * @var int
     */
    private int $order;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getProduct(): int
    {
        return $this->product;
    }

    /**
     * @param int $product
     */
    public function setProduct(int $product): void
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
