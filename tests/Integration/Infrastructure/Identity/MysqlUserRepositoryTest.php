<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Identity;

use App\Domain\Identity\Email;
use App\Domain\Identity\PasswordHash;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use Ramsey\Uuid\Uuid;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\IntegrityException;
use Yiisoft\Yii\Runner\Console\ConsoleApplicationRunner;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class MysqlUserRepositoryTest extends Unit
{
    private ConnectionInterface $connection;
    private UserRepository $repository;
    private array $createdUserIds = [];

    protected function _before(): void
    {
        $runner = new ConsoleApplicationRunner(dirname(__DIR__, 4), environment: 'test');
        $container = $runner->getContainer();

        $this->connection = $container->get(ConnectionInterface::class);
        $this->repository = $container->get(UserRepository::class);

        if ($this->connection->createCommand('SELECT DATABASE()')->queryScalar() !== 'manager_hub_test') {
            throw new LogicException('Integration tests must use the manager_hub_test database.');
        }
    }

    protected function _after(): void
    {
        foreach ($this->createdUserIds as $userId) {
            $this->connection
                ->createCommand()
                ->delete('users', ['id' => $userId])
                ->execute();
        }

        $this->connection->close();
    }

    public function testAddsAUserAndPreservesItsPersistedState(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00.123456');
        $user = $this->registeredUser($createdAt);

        $this->add($user);

        $row = $this->connection
            ->select('*')
            ->from('users')
            ->where(['id' => $user->id->toString()])
            ->one();

        assertSame($user->id->toString(), $row['id']);
        assertSame($user->username->value, $row['username']);
        assertSame($user->email->value, $row['email']);
        assertSame('password-hash', $row['password_hash']);
        assertSame('2026-09-04 10:00:00.123456', $row['created_at']);
        assertSame('2026-09-04 10:00:00.123456', $row['updated_at']);
        assertFalse((bool) $row['is_deleted']);
        assertSame(null, $row['deleted_at']);
    }

    public function testChecksAvailabilityUsingTheDatabaseUniquenessSemantics(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00.123456');
        $user = $this->registeredUser($createdAt);
        $this->add($user);

        assertTrue($this->repository->existsByUsername(new Username(strtolower($user->username->value))));
        assertTrue($this->repository->existsByEmail($user->email));
        assertSame($user->id->toString(), $this->repository->findByEmail($user->email)?->id->toString());
        assertFalse($this->repository->existsByUsername(new Username('OtherUser')));
        assertFalse($this->repository->existsByEmail(new Email('other@example.com')));
    }

    public function testTreatsDeletedUsersAsReservedIdentifiers(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00.123456');
        $deletedAt = $this->utc('2026-09-05 10:00:00.123456');
        $username = $this->uniqueUsername();
        $email = new Email($username->value . '@example.com');
        $user = User::reconstitute(
            Uuid::uuid7($createdAt),
            $username,
            $email,
            new PasswordHash('password-hash'),
            $createdAt,
            $deletedAt,
            true,
            $deletedAt,
        );
        $this->add($user);

        assertTrue($this->repository->existsByUsername(new Username(strtolower($username->value))));
        assertTrue($this->repository->existsByEmail($email));
        assertSame(null, $this->repository->findByUsername($username));
        assertSame(null, $this->repository->findByEmail($email));
    }

    public function testThrowsAnIntegrityExceptionForADuplicateIdentifier(): void
    {
        $createdAt = $this->utc('2026-09-04 10:00:00.123456');
        $user = $this->registeredUser($createdAt);
        $otherUsername = $this->uniqueUsername();
        $duplicate = User::register(
            $user->username,
            new Email($otherUsername->value . '@example.com'),
            new PasswordHash('password-hash'),
            $createdAt,
        );
        $this->add($user);
        $this->createdUserIds[] = $duplicate->id->toString();

        $this->expectException(IntegrityException::class);

        $this->repository->add($duplicate);
    }

    private function utc(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }

    private function add(User $user): void
    {
        $this->createdUserIds[] = $user->id->toString();

        $this->repository->add($user);
    }

    private function registeredUser(DateTimeImmutable $occurredAt): User
    {
        $username = $this->uniqueUsername();

        return User::register(
            $username,
            new Email($username->value . '@example.com'),
            new PasswordHash('password-hash'),
            $occurredAt,
        );
    }

    private function uniqueUsername(): Username
    {
        return new Username('Test' . bin2hex(random_bytes(6)));
    }
}
