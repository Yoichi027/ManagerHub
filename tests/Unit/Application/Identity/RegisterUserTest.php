<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\PasswordHasher;
use App\Application\Identity\PasswordPolicy;
use App\Application\Identity\RegisterUser\RegisterUser;
use App\Application\Identity\RegisterUser\RegisterUserCommand;
use App\Application\Identity\RegisterUser\RegisterUserResult;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\Email;
use App\Domain\Identity\PasswordHash;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\UuidInterface;
use Psr\Log\AbstractLogger;
use Stringable;
use Yiisoft\Db\Exception\IntegrityException;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class RegisterUserTest extends Unit
{
    public function testRegistersAnAvailableUser(): void
    {
        $repository = new FakeUserRepository();
        $hasher = new FakePasswordHasher();

        $result = $this->registerUser($repository, $hasher)->register(
            new RegisterUserCommand('Tiago42', 'tiago@example.com', 'Correct horse 7! battery'),
        );

        assertSame(RegisterUserResult::Registered, $result);
        assertSame(['Correct horse 7! battery'], $hasher->passwords);
        assertCount(1, $repository->addedUsers);
        assertSame('Tiago42', $repository->addedUsers[0]->username->value);
        assertSame('tiago@example.com', $repository->addedUsers[0]->email->value);
        assertSame('hashed-password', $repository->addedUsers[0]->passwordHash->value);
    }

    public function testRejectsAnExistingUsernameBeforeHashingThePassword(): void
    {
        $repository = new FakeUserRepository();
        $repository->usernameExists = true;
        $hasher = new FakePasswordHasher();

        $result = $this->registerUser($repository, $hasher)->register(
            new RegisterUserCommand('Tiago42', 'tiago@example.com', 'Correct horse 7! battery'),
        );

        assertSame(RegisterUserResult::UsernameTaken, $result);
        assertSame([], $hasher->passwords);
        assertSame([], $repository->addedUsers);
    }

    public function testRejectsAnExistingEmailBeforeHashingThePassword(): void
    {
        $repository = new FakeUserRepository();
        $repository->emailExists = true;
        $hasher = new FakePasswordHasher();

        $result = $this->registerUser($repository, $hasher)->register(
            new RegisterUserCommand('Tiago42', 'tiago@example.com', 'Correct horse 7! battery'),
        );

        assertSame(RegisterUserResult::EmailTaken, $result);
        assertSame([], $hasher->passwords);
        assertSame([], $repository->addedUsers);
    }

    public function testRejectsInvalidInputBeforeQueryingTheRepository(): void
    {
        $repository = new FakeUserRepository();
        $hasher = new FakePasswordHasher();

        $result = $this->registerUser($repository, $hasher)->register(
            new RegisterUserCommand('invalid_username', 'tiago@example.com', 'Correct horse 7! battery'),
        );

        assertSame(RegisterUserResult::InvalidInput, $result);
        assertSame(0, $repository->usernameChecks);
        assertSame(0, $repository->emailChecks);
        assertSame([], $hasher->passwords);
    }

    public function testTranslatesAUsernameCollisionDuringInsertion(): void
    {
        $repository = new FakeUserRepository();
        $repository->collision = 'username';
        $logger = new RecordingLogger();

        $result = $this->registerUser($repository, new FakePasswordHasher(), $logger)->register(
            new RegisterUserCommand('Tiago42', 'tiago@example.com', 'Correct horse 7! battery'),
        );

        assertSame(RegisterUserResult::UsernameTaken, $result);
        assertCount(1, $logger->records);
        assertSame('warning', $logger->records[0]['level']);
        assertSame('user_registration_unique_constraint_race', $logger->records[0]['context']['event']);
        assertTrue($logger->records[0]['context']['username_taken']);
        assertFalse($logger->records[0]['context']['email_taken']);
    }

    private function registerUser(
        FakeUserRepository $repository,
        FakePasswordHasher $hasher,
        ?RecordingLogger $logger = null,
    ): RegisterUser {
        return new RegisterUser(
            $repository,
            $hasher,
            new FixedUtcClock(),
            new PasswordPolicy(),
            $logger ?? new RecordingLogger(),
        );
    }
}

final class FakeUserRepository implements UserRepository
{
    public bool $usernameExists = false;
    public bool $emailExists = false;
    public int $usernameChecks = 0;
    public int $emailChecks = 0;
    public array $addedUsers = [];
    public ?string $collision = null;

    public function existsByUsername(Username $username): bool
    {
        $this->usernameChecks++;

        return $this->usernameExists;
    }

    public function existsByEmail(Email $email): bool
    {
        $this->emailChecks++;

        return $this->emailExists;
    }

    public function findByUsername(Username $username): ?User
    {
        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        return null;
    }

    public function findById(UuidInterface $id): ?User
    {
        return null;
    }

    public function add(User $user): void
    {
        if ($this->collision === 'username') {
            $this->usernameExists = true;
            throw new IntegrityException('Duplicate username.');
        }

        if ($this->collision === 'email') {
            $this->emailExists = true;
            throw new IntegrityException('Duplicate email.');
        }

        $this->addedUsers[] = $user;
    }

    public function save(User $user): void {}
}

final class FakePasswordHasher implements PasswordHasher
{
    public array $passwords = [];

    public function hash(string $password): PasswordHash
    {
        $this->passwords[] = $password;

        return new PasswordHash('hashed-password');
    }

    public function verify(string $password, PasswordHash $hash): bool
    {
        return false;
    }
}

final class FixedUtcClock implements UtcClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-04 10:00:00', new DateTimeZone('UTC'));
    }
}

final class RecordingLogger extends AbstractLogger
{
    public array $records = [];

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
