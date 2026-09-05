<?php

declare(strict_types=1);

namespace App\Application\Identity\AuthenticateUser;

use App\Application\Identity\PasswordHasher;
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
            $username = new Username($command->username);
        } catch (DomainException) {
            return null;
        }

        $user = $this->users->findByUsername($username);

        if ($user === null || !$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}
