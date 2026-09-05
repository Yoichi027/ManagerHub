<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity\ReactivateAccount;

use App\Application\Identity\PasswordHasher;
use App\Application\Identity\ReactivateAccount\ReactivateAccount;
use App\Application\Identity\ReactivateAccount\ReactivateAccountCommand;
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

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

final class ReactivateAccountTest extends Unit
{
    public function testReactivatesADeactivatedAccountWithItsCredentials(): void
    {
        $user = $this->deactivatedUser();
        $repository = new ReactivationUserRepository($user);
        $hasher = new ReactivationPasswordHasher(true);

        $result = (new ReactivateAccount($repository, $hasher, new ReactivationClock()))->reactivate(
            new ReactivateAccountCommand('Tiago@Example.com', 'Password1!'),
        );

        assertSame($user, $result);
        assertFalse($user->isDeleted);
        assertSame([$user], $repository->savedUsers);
        assertSame(['Password1!'], $hasher->passwords);
    }

    public function testRejectsInvalidCredentialsWithoutRestoringTheAccount(): void
    {
        $user = $this->deactivatedUser();
        $repository = new ReactivationUserRepository($user);

        $result = (new ReactivateAccount($repository, new ReactivationPasswordHasher(false), new ReactivationClock()))->reactivate(
            new ReactivateAccountCommand('Tiago42', 'WrongPassword1!'),
        );

        assertNull($result);
        assertSame([], $repository->savedUsers);
    }

    private function deactivatedUser(): User
    {
        $createdAt = new DateTimeImmutable('2026-09-04 10:00:00', new DateTimeZone('UTC'));
        $user = User::register(new Username('Tiago42'), new Email('tiago@example.com'), new PasswordHash('password-hash'), $createdAt);
        $user->delete(new DateTimeImmutable('2026-09-05 10:00:00', new DateTimeZone('UTC')));

        return $user;
    }
}

final class ReactivationUserRepository implements UserRepository
{
    /** @var list<User> */
    public array $savedUsers = [];

    public function __construct(private ?User $user) {}

    public function existsByUsername(Username $username): bool { return false; }

    public function existsByEmail(Email $email): bool { return false; }

    public function findByUsername(Username $username): ?User { return null; }

    public function findByEmail(Email $email): ?User { return null; }

    public function findByUsernameIncludingDeleted(Username $username): ?User { return $this->user; }

    public function findByEmailIncludingDeleted(Email $email): ?User { return $this->user; }

    public function findById(UuidInterface $id): ?User { return null; }

    public function add(User $user): void {}

    public function save(User $user): void { $this->savedUsers[] = $user; }
}

final class ReactivationPasswordHasher implements PasswordHasher
{
    /** @var list<string> */
    public array $passwords = [];

    public function __construct(private bool $valid) {}

    public function hash(string $password): PasswordHash { return new PasswordHash('unused'); }

    public function verify(string $password, PasswordHash $hash): bool
    {
        $this->passwords[] = $password;

        return $this->valid;
    }
}

final class ReactivationClock implements UtcClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-06 10:00:00', new DateTimeZone('UTC'));
    }
}
