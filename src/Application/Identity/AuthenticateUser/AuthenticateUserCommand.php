<?php

declare(strict_types=1);

namespace App\Application\Identity\AuthenticateUser;

final readonly class AuthenticateUserCommand
{
    public function __construct(
        public string $username,
        public string $password,
    ) {}
}
