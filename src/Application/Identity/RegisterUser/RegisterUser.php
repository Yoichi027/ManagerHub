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

        if ($this->users->existsByUsername($username)) {
            return RegisterUserResult::UsernameTaken;
        }

        if ($this->users->existsByEmail($email)) {
            return RegisterUserResult::EmailTaken;
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
            $usernameTaken = $this->users->existsByUsername($username);
            $emailTaken = $this->users->existsByEmail($email);

            if (!$usernameTaken && !$emailTaken) {
                throw $exception;
            }

            $this->logger->warning('User registration collided with a unique constraint.', [
                'event' => 'user_registration_unique_constraint_race',
                'username_taken' => $usernameTaken,
                'email_taken' => $emailTaken,
            ]);

            return $usernameTaken
                ? RegisterUserResult::UsernameTaken
                : RegisterUserResult::EmailTaken;
        }

        return RegisterUserResult::Registered;
    }
}
