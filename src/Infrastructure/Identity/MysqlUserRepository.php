<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Domain\Identity\Email;
use App\Domain\Identity\PasswordHash;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlUserRepository implements UserRepository, IdentityRepositoryInterface
{
    public function __construct(private ConnectionInterface $connection) {}

    public function existsByUsername(Username $username): bool
    {
        return $this->connection
            ->select('id')
            ->from('users')
            ->where(['username' => $username->value])
            ->exists();
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->connection
            ->select('id')
            ->from('users')
            ->where(['email' => $email->value])
            ->exists();
    }

    public function findByUsername(Username $username): ?User
    {
        return $this->findActiveUser(['username' => $username->value]);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->findActiveUser(['email' => $email->value]);
    }

    public function findByUsernameIncludingDeleted(Username $username): ?User
    {
        return $this->findUser(['username' => $username->value]);
    }

    public function findByEmailIncludingDeleted(Email $email): ?User
    {
        return $this->findUser(['email' => $email->value]);
    }

    public function findById(\Ramsey\Uuid\UuidInterface $id): ?User
    {
        return $this->findActiveUser(['id' => $id->toString()]);
    }

    public function findIdentity(string $id): ?IdentityInterface
    {
        $userId = $this->connection
            ->select('id')
            ->from('users')
            ->where(['id' => $id, 'is_deleted' => false])
            ->scalar();

        return $userId === null ? null : new AuthenticatedUserIdentity((string) $userId);
    }

    public function add(User $user): void
    {
        $this->connection
            ->createCommand()
            ->insert('users', [
                'id' => $user->id->toString(),
                'username' => $user->username->value,
                'email' => $user->email->value,
                'password_hash' => $user->passwordHash->value,
                'created_at' => $this->formatInstant($user->createdAt),
                'updated_at' => $this->formatInstant($user->updatedAt),
                'is_deleted' => $user->isDeleted,
                'deleted_at' => $user->deletedAt === null ? null : $this->formatInstant($user->deletedAt),
            ])
            ->execute();
    }

    public function save(User $user): void
    {
        $this->connection
            ->createCommand()
            ->update('users', [
                'email' => $user->email->value,
                'password_hash' => $user->passwordHash->value,
                'updated_at' => $this->formatInstant($user->updatedAt),
                'is_deleted' => $user->isDeleted,
                'deleted_at' => $user->deletedAt === null ? null : $this->formatInstant($user->deletedAt),
            ], ['id' => $user->id->toString()])
            ->execute();
    }

    private function formatInstant(DateTimeImmutable $instant): string
    {
        return $instant->format('Y-m-d H:i:s.u');
    }

    /** @param array<string, string> $condition */
    private function findActiveUser(array $condition): ?User
    {
        return $this->findUser([...$condition, 'is_deleted' => false]);
    }

    /** @param array<string, string|bool> $condition */
    private function findUser(array $condition): ?User
    {
        $row = $this->connection
            ->select('*')
            ->from('users')
            ->where($condition)
            ->one();

        return $row === null ? null : $this->reconstitute($row);
    }

    /** @param array<string, mixed> $row */
    private function reconstitute(array $row): User
    {
        return User::reconstitute(
            Uuid::fromString((string) $row['id']),
            new Username((string) $row['username']),
            new Email((string) $row['email']),
            new PasswordHash((string) $row['password_hash']),
            $this->parseInstant((string) $row['created_at']),
            $this->parseInstant((string) $row['updated_at']),
            (bool) $row['is_deleted'],
            $row['deleted_at'] === null ? null : $this->parseInstant((string) $row['deleted_at']),
        );
    }

    private function parseInstant(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
