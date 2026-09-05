<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class User
{
    public readonly UuidInterface $id;
    public readonly Username $username;
    public private(set) Email $email;
    public private(set) PasswordHash $passwordHash;
    public readonly DateTimeImmutable $createdAt;
    public private(set) DateTimeImmutable $updatedAt;
    public private(set) bool $isDeleted;
    public private(set) ?DateTimeImmutable $deletedAt;

    private function __construct(
        UuidInterface $id,
        Username $username,
        Email $email,
        PasswordHash $passwordHash,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt,
    ) {
        UtcInstant::assert($createdAt, $updatedAt, $deletedAt);

        if ($updatedAt < $createdAt) {
            throw new DomainException('Updated-at timestamp cannot precede created-at timestamp.');
        }

        if ($isDeleted && $deletedAt === null) {
            throw new DomainException('A deleted user must have a deleted-at timestamp.');
        }

        if ($deletedAt !== null && ($deletedAt < $createdAt || $deletedAt > $updatedAt)) {
            throw new DomainException('Deleted-at timestamp must fall between creation and last update.');
        }

        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
        $this->deletedAt = $deletedAt;
    }

    /**
     * Creates a new user at the supplied UTC instant.
     */
    public static function register(
        Username $username,
        Email $email,
        PasswordHash $passwordHash,
        DateTimeImmutable $occurredAt,
    ): self {
        return new self(
            Uuid::uuid7($occurredAt),
            $username,
            $email,
            $passwordHash,
            $occurredAt,
            $occurredAt,
            false,
            null,
        );
    }

    /**
     * Restores a user previously persisted by a repository.
     */
    public static function reconstitute(
        UuidInterface $id,
        Username $username,
        Email $email,
        PasswordHash $passwordHash,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $username,
            $email,
            $passwordHash,
            $createdAt,
            $updatedAt,
            $isDeleted,
            $deletedAt,
        );
    }

    /**
     * Marks this user as deleted at the supplied UTC instant.
     */
    public function delete(DateTimeImmutable $occurredAt): void
    {
        if ($this->isDeleted) {
            throw new DomainException('Cannot delete an already deleted user.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Deleted-at timestamp cannot precede updated-at timestamp.');
        }

        $this->isDeleted = true;
        $this->deletedAt = $occurredAt;
        $this->updatedAt = $occurredAt;
    }

    /**
     * Restores this user at the supplied UTC instant while preserving the last deletion timestamp.
     */
    public function restore(DateTimeImmutable $occurredAt): void
    {
        if (!$this->isDeleted) {
            throw new DomainException('Cannot restore an active user.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Restored-at timestamp cannot precede updated-at timestamp.');
        }

        $this->isDeleted = false;
        $this->updatedAt = $occurredAt;
    }

    public function changeEmail(Email $email, DateTimeImmutable $occurredAt): void
    {
        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Email change timestamp cannot precede updated-at timestamp.');
        }

        $this->email = $email;
        $this->updatedAt = $occurredAt;
    }

    public function changePassword(PasswordHash $passwordHash, DateTimeImmutable $occurredAt): void
    {
        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Password change timestamp cannot precede updated-at timestamp.');
        }

        $this->passwordHash = $passwordHash;
        $this->updatedAt = $occurredAt;
    }

}
