<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangePassword;

use App\Application\Identity\PasswordHasher;
use App\Application\Identity\PasswordPolicy;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\UserRepository;

final readonly class ChangePassword
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private PasswordPolicy $passwordPolicy,
        private UtcClock $clock,
    ) {}

    public function change(ChangePasswordCommand $command): ChangePasswordResult
    {
        $user = $this->users->findById($command->userId);

        if ($user === null) {
            return ChangePasswordResult::UserNotFound;
        }

        if (!$this->passwordHasher->verify($command->currentPassword, $user->passwordHash)) {
            return ChangePasswordResult::CurrentPasswordIncorrect;
        }

        if (!$this->passwordPolicy->isValid($command->newPassword)) {
            return ChangePasswordResult::InvalidPassword;
        }

        $user->changePassword($this->passwordHasher->hash($command->newPassword), $this->clock->now());
        $this->users->save($user);

        return ChangePasswordResult::Changed;
    }
}
