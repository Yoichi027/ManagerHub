<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\AuthenticateUser\AuthenticateUser;
use App\Application\Identity\AuthenticateUser\AuthenticateUserCommand;
use App\Application\Identity\PasswordHasher;
use App\Domain\Identity\Email;
use App\Domain\Identity\PasswordHash;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;

use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

final class AuthenticateUserTest extends Unit
{
    public function testAuthenticatesAnActiveUserWithValidCredentials(): void
    {
        $repository = new AuthenticationUserRepository();
        $repository->user = $this->user();
        $hasher = new AuthenticationPasswordHasher();
        $hasher->valid = true;

        $user = (new AuthenticateUser($repository, $hasher))->authenticate(
            new AuthenticateUserCommand('Tiago42', 'Password1!'),
        );

        assertSame($repository->user, $user);
        assertSame(['Password1!'], $hasher->passwords);
    }

    public function testRejectsUnknownOrInvalidCredentialsWithoutRevealingWhichFailed(): void
    {
        $repository = new AuthenticationUserRepository();
        $hasher = new AuthenticationPasswordHasher();

        assertNull((new AuthenticateUser($repository, $hasher))->authenticate(
            new AuthenticateUserCommand('Unknown', 'Password1!'),
        ));
        assertSame([], $hasher->passwords);

        $repository->user = $this->user();
        assertNull((new AuthenticateUser($repository, $hasher))->authenticate(
            new AuthenticateUserCommand('Tiago42', 'WrongPassword1!'),
        ));
        assertSame(['WrongPassword1!'], $hasher->passwords);
    }

    public function testRejectsAnInvalidUsernameBeforeLookingUpTheUser(): void
    {
        $repository = new AuthenticationUserRepository();

        assertNull((new AuthenticateUser($repository, new AuthenticationPasswordHasher()))->authenticate(
            new AuthenticateUserCommand('not valid', 'Password1!'),
        ));
        assertSame(0, $repository->lookups);
    }

    private function user(): User
    {
        return User::register(
            new Username('Tiago42'),
            new Email('tiago@example.com'),
            new PasswordHash('password-hash'),
            new DateTimeImmutable('2026-09-05 10:00:00', new DateTimeZone('UTC')),
        );
    }
}

final class AuthenticationUserRepository implements UserRepository
{
    public ?User $user = null;
    public int $lookups = 0;

    public function existsByUsername(Username $username): bool
    {
        return false;
    }

    public function existsByEmail(Email $email): bool
    {
        return false;
    }

    public function findByUsername(Username $username): ?User
    {
        $this->lookups++;
        return $this->user;
    }

    public function add(User $user): void {}
}

final class AuthenticationPasswordHasher implements PasswordHasher
{
    public bool $valid = false;
    public array $passwords = [];

    public function hash(string $password): PasswordHash
    {
        return new PasswordHash('unused');
    }

    public function verify(string $password, PasswordHash $hash): bool
    {
        $this->passwords[] = $password;
        return $this->valid;
    }
}
