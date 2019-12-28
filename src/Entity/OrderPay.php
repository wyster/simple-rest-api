<?php declare(strict_types=1);

namespace App\Entity;

use Money\Money;

class OrderPay
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var Money
     */
    private Money $amount;

    /**
     * @return int
     */
    public function getId(): int
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
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @param Money $amount
     */
    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }
}
