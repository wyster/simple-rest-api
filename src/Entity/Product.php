<?php declare(strict_types=1);

namespace App\Entity;

use Money\Money;

class Product extends AbstractEntity
{
    /**
     * @var null|int
     */
    private ?int $id = null;

    /**
     * @var string
     */
    private string $title;

    /**Entity/Product.php:29

     * @var Money
     */
    private Money $price;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @param Money $price
     */
    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }
}
