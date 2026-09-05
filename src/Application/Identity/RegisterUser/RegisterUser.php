<?php

declare(strict_types=1);

namespace App\Application\Identity\RegisterUser;

use App\Application\Identity\PasswordHasher;
use App\Application\Identity\PasswordPolicy;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\Email;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use DomainException;
use Psr\Log\LoggerInterface;
use Yiisoft\Db\Exception\IntegrityException;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private UtcClock $clock,
        private PasswordPolicy $passwordPolicy,
        private LoggerInterface $logger,
    ) {}

    public function register(RegisterUserCommand $command): RegisterUserResult
    {
        try {
            $username = new Username($command->username);
            $email = new Email($command->email);
        } catch (DomainException) {
            return RegisterUserResult::InvalidInput;
        }

        if (!$this->passwordPolicy->isValid($command->password)) {
            return RegisterUserResult::InvalidInput;
        }

        $userWithUsername = $this->users->findByUsernameIncludingDeleted($username);

        if ($userWithUsername !== null) {
            return $userWithUsername->isDeleted
                ? RegisterUserResult::DeactivatedAccount
                : RegisterUserResult::UsernameTaken;
        }

        $userWithEmail = $this->users->findByEmailIncludingDeleted($email);

        if ($userWithEmail !== null) {
            return $userWithEmail->isDeleted
                ? RegisterUserResult::DeactivatedAccount
                : RegisterUserResult::EmailTaken;
        }

        $user = User::register(
            $username,
            $email,
            $this->passwordHasher->hash($command->password),
            $this->clock->now(),
        );

        try {
            $this->users->add($user);
        } catch (IntegrityException $exception) {
            $userWithUsername = $this->users->findByUsernameIncludingDeleted($username);
            $userWithEmail = $this->users->findByEmailIncludingDeleted($email);
            $usernameTaken = $userWithUsername !== null;
            $emailTaken = $userWithEmail !== null;

            if (!$usernameTaken && !$emailTaken) {
                throw $exception;
            }

            $this->logger->warning('User registration collided with a unique constraint.', [
                'event' => 'user_registration_unique_constraint_race',
                'username_taken' => $usernameTaken,
                'email_taken' => $emailTaken,
            ]);

            if (($userWithUsername ?? $userWithEmail)?->isDeleted) {
                return RegisterUserResult::DeactivatedAccount;
            }

            return $usernameTaken ? RegisterUserResult::UsernameTaken : RegisterUserResult::EmailTaken;
        }

        return RegisterUserResult::Registered;
    }
}
