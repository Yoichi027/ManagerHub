<?php

declare(strict_types=1);

namespace App\Application\Identity\ReactivateAccount;

use App\Application\Identity\PasswordHasher;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\Email;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use DomainException;

final readonly class ReactivateAccount
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private UtcClock $clock,
    ) {}

    public function reactivate(ReactivateAccountCommand $command): ?User
    {
        try {
            $user = str_contains($command->identifier, '@')
                ? $this->users->findByEmailIncludingDeleted(new Email($command->identifier))
                : $this->users->findByUsernameIncludingDeleted(new Username($command->identifier));
        } catch (DomainException) {
            return null;
        }

        if ($user === null || !$user->isDeleted || !$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            return null;
        }

        $user->restore($this->clock->now());
        $this->users->save($user);

        return $user;
    }
}
