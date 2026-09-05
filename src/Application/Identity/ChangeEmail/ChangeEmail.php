<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangeEmail;

use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\Email;
use App\Domain\Identity\UserRepository;
use DomainException;

final readonly class ChangeEmail
{
    public function __construct(private UserRepository $users, private UtcClock $clock) {}

    public function change(ChangeEmailCommand $command): ChangeEmailResult
    {
        try {
            $email = new Email($command->email);
        } catch (DomainException) {
            return ChangeEmailResult::InvalidInput;
        }

        $user = $this->users->findById($command->userId);

        if ($user === null) {
            return ChangeEmailResult::UserNotFound;
        }

        if ($user->email->value !== $email->value && $this->users->existsByEmail($email)) {
            return ChangeEmailResult::EmailTaken;
        }

        $user->changeEmail($email, $this->clock->now());
        $this->users->save($user);

        return ChangeEmailResult::Changed;
    }
}
