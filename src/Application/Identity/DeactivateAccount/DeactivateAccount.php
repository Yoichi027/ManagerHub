<?php

declare(strict_types=1);

namespace App\Application\Identity\DeactivateAccount;

use App\Application\Identity\PasswordHasher;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\UserRepository;

final readonly class DeactivateAccount
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private UtcClock $clock,
    ) {}

    public function deactivate(DeactivateAccountCommand $command): DeactivateAccountResult
    {
        $user = $this->users->findById($command->userId);

        if ($user === null) {
            return DeactivateAccountResult::UserNotFound;
        }

        if (!$this->passwordHasher->verify($command->password, $user->passwordHash)) {
            return DeactivateAccountResult::PasswordIncorrect;
        }

        $user->delete($this->clock->now());
        $this->users->save($user);

        return DeactivateAccountResult::Deactivated;
    }
}
