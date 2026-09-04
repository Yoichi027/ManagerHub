<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Domain\Identity\Email;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use DateTimeImmutable;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlUserRepository implements UserRepository
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

    private function formatInstant(DateTimeImmutable $instant): string
    {
        return $instant->format('Y-m-d H:i:s.u');
    }
}
