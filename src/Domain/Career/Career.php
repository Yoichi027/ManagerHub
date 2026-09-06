<?php

declare(strict_types=1);

namespace App\Domain\Career;

use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Career
{
    public readonly UuidInterface $id;
    public readonly UuidInterface $userId;
    public private(set) CareerName $name;
    public private(set) ManagerName $managerName;
    public readonly GameEdition $gameEdition;
    public readonly DateTimeImmutable $createdAt;
    public private(set) DateTimeImmutable $updatedAt;
    public private(set) bool $isDeleted;
    public private(set) ?DateTimeImmutable $deletedAt;
    public private(set) ?DateTimeImmutable $lastViewedAt;

    private function __construct(
        UuidInterface $id,
        UuidInterface $userId,
        CareerName $name,
        ManagerName $managerName,
        GameEdition $gameEdition,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt, ?DateTimeImmutable $lastViewedAt,
    ) {
        UtcInstant::assert($createdAt, $updatedAt, $deletedAt, $lastViewedAt);

        if ($updatedAt < $createdAt) {
            throw new DomainException('Updated-at timestamp cannot precede created-at timestamp.');
        }

        if ($isDeleted && $deletedAt === null) {
            throw new DomainException('A deleted career must have a deleted-at timestamp.');
        }

        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->managerName = $managerName;
        $this->gameEdition = $gameEdition;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
        $this->deletedAt = $deletedAt;
        $this->lastViewedAt = $lastViewedAt;
    }

    public static function start(CareerName $name, ManagerName $managerName, GameEdition $gameEdition, UuidInterface $userId, DateTimeImmutable $occurredAt): self
    {
        return new self(Uuid::uuid7($occurredAt), $userId, $name, $managerName, $gameEdition, $occurredAt, $occurredAt, false, null, $occurredAt);
    }

    public static function reconstitute(
        UuidInterface $id,
        UuidInterface $userId,
        CareerName $name,
        ManagerName $managerName,
        GameEdition $gameEdition,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt, ?DateTimeImmutable $lastViewedAt,
    ): self {
        return new self($id, $userId, $name, $managerName, $gameEdition, $createdAt, $updatedAt, $isDeleted, $deletedAt, $lastViewedAt);
    }

    public function viewed(DateTimeImmutable $occurredAt): void { UtcInstant::assert($occurredAt); $this->lastViewedAt = $occurredAt; }

    public function delete(DateTimeImmutable $occurredAt): void
    {
        if ($this->isDeleted) {
            throw new DomainException('Cannot delete an already deleted career.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Deleted-at timestamp cannot precede updated-at timestamp.');
        }

        $this->isDeleted = true;
        $this->deletedAt = $occurredAt;
        $this->updatedAt = $occurredAt;
    }

    public function restore(DateTimeImmutable $occurredAt): void
    {
        if (!$this->isDeleted) {
            throw new DomainException('Cannot restore an active career.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Restored-at timestamp cannot precede updated-at timestamp.');
        }

        $this->isDeleted = false;
        $this->updatedAt = $occurredAt;
    }
}
