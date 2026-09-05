<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use Yiisoft\Auth\IdentityInterface;

final readonly class AuthenticatedUserIdentity implements IdentityInterface
{
    public function __construct(private string $id) {}

    public function getId(): ?string
    {
        return $this->id;
    }
}
