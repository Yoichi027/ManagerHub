<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Identity;

use App\Domain\Identity\Email;
use App\Domain\Identity\PasswordHash;
use App\Domain\Identity\User;
use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Ramsey\Uuid\Uuid;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class UserTest extends Unit
{
    public function testRegisterCreatesAnActiveUserWithUuidVersionSeven(): void
    {
        $occurredAt = $this->utc('2026-09-04 10:00:00');

        $user = User::register(
            new Username('Tiago42'),
            new Email('tiago@example.com'),
            new PasswordHash('password-hash'),
            $occurredAt,
        );

        assertSame(7, $user->id->getVersion());
        assertSame($occurredAt, $user->createdAt);
        assertSame($occurredAt, $user->updatedAt);
        assertFalse($user->isDeleted);
        assertSame(null, $user->deletedAt);
    }

    public function testDeleteThenRestorePreservesTheLastDeletionTimestamp(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00');
        $deletedAt = $this->utc('2026-09-05 10:00:00');
        $restoredAt = $this->utc('2026-09-06 10:00:00');
        $user = $this->registeredUser($createdAt);

        $user->delete($deletedAt);
        $user->restore($restoredAt);

        assertFalse($user->isDeleted);
        assertSame($deletedAt, $user->deletedAt);
        assertSame($restoredAt, $user->updatedAt);
    }

    public function testDeleteRejectsAnAlreadyDeletedUser(): void
    {
        $user = $this->registeredUser($this->utc('2026-09-04 10:00:00'));
        $user->delete($this->utc('2026-09-05 10:00:00'));

        $this->expectException(DomainException::class);

        $user->delete($this->utc('2026-09-06 10:00:00'));
    }

    public function testRestoreRejectsAnActiveUser(): void
    {
        $user = $this->registeredUser($this->utc('2026-09-04 10:00:00'));

        $this->expectException(DomainException::class);

        $user->restore($this->utc('2026-09-05 10:00:00'));
    }

    public function testLifecycleEventsCannotPrecedeTheLastUpdate(): void
    {
        $user = $this->registeredUser($this->utc('2026-09-04 10:00:00'));
        $user->delete($this->utc('2026-09-05 10:00:00'));

        $this->expectException(DomainException::class);

        $user->restore($this->utc('2026-09-04 12:00:00'));
    }

    public function testReconstituteAllowsADeletedUserAndItsPersistedState(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00');
        $deletedAt = $this->utc('2026-09-05 10:00:00');
        $updatedAt = $this->utc('2026-09-06 10:00:00');
        $id = Uuid::uuid7($createdAt);

        $user = User::reconstitute(
            $id,
            new Username('Tiago42'),
            new Email('tiago@example.com'),
            new PasswordHash('password-hash'),
            $createdAt,
            $updatedAt,
            true,
            $deletedAt,
        );

        assertSame($id, $user->id);
        assertTrue($user->isDeleted);
        assertSame($deletedAt, $user->deletedAt);
        assertSame($updatedAt, $user->updatedAt);
    }

    public function testRejectsNonUtcInstants(): void
    {
        $this->expectException(DomainException::class);

        User::register(
            new Username('Tiago42'),
            new Email('tiago@example.com'),
            new PasswordHash('password-hash'),
            new DateTimeImmutable('2026-09-04 10:00:00', new DateTimeZone('Europe/Lisbon')),
        );
    }

    private function registeredUser(DateTimeImmutable $occurredAt): User
    {
        return User::register(
            new Username('Tiago42'),
            new Email('tiago@example.com'),
            new PasswordHash('password-hash'),
            $occurredAt,
        );
    }

    private function utc(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
