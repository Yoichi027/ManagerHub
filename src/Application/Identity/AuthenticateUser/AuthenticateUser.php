<?php

declare(strict_types=1);

namespace App\Application\Identity\AuthenticateUser;

use App\Application\Identity\PasswordHasher;
use App\Domain\Identity\Email;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use DomainException;

final readonly class AuthenticateUser
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
    ) {}

    public function authenticate(AuthenticateUserCommand $command): ?User
    {
        try {
            $user = str_contains($command->identifier, '@')
                ? $this->users->findByEmail(new Email($command->identifier))
                : $this->users->findByUsername(new Username($command->identifier));
        } catch (DomainException) {
            return null;
        }

        if ($user === null || !$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}
