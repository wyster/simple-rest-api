<?php declare(strict_types=1);

namespace App\Service\Auth;

class FakeIdentity implements IdentityInterface
{
    /**
     * @return int
     */
    public function getId(): int
    {
        return 1;
    }
}
