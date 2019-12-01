<?php declare(strict_types=1);

namespace App\Service\Auth;

interface IdentityInterface
{
    public function getId(): int;
}
