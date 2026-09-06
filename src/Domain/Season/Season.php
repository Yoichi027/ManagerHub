<?php

declare(strict_types=1);

namespace App\Domain\Season;

use App\Domain\Shared\CalendarDate;
use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Season
{
    public readonly UuidInterface $id;
    public readonly UuidInterface $careerId;
    public readonly ManagedClub $managedClub;
    public readonly ManagedLeague $managedLeague;
    public readonly SeasonLabel $label;
    public readonly CalendarDate $startsOn;
    public readonly CalendarDate $endsOn;
    public private(set) ?DateTimeImmutable $finalizedAt;
    public readonly DateTimeImmutable $createdAt;
    public private(set) DateTimeImmutable $updatedAt;
    public private(set) bool $isDeleted;
    public private(set) ?DateTimeImmutable $deletedAt;

    private function __construct(
        UuidInterface $id,
        UuidInterface $careerId,
        ManagedClub $managedClub,
        ManagedLeague $managedLeague,
        SeasonLabel $label,
        CalendarDate $startsOn,
        CalendarDate $endsOn,
        ?DateTimeImmutable $finalizedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt,
    ) {
        UtcInstant::assert($finalizedAt, $createdAt, $updatedAt, $deletedAt);

        if ($endsOn->isBefore($startsOn)) {
            throw new DomainException('Season end date cannot precede its start date.');
        }

        if ($updatedAt < $createdAt) {
            throw new DomainException('Updated-at timestamp cannot precede created-at timestamp.');
        }

        if ($finalizedAt !== null && $finalizedAt < $createdAt) {
            throw new DomainException('Finalized-at timestamp cannot precede season creation.');
        }

        if ($isDeleted && $deletedAt === null) {
            throw new DomainException('A deleted season must have a deleted-at timestamp.');
        }

        $this->id = $id;
        $this->careerId = $careerId;
        $this->managedClub = $managedClub;
        $this->managedLeague = $managedLeague;
        $this->label = $label;
        $this->startsOn = $startsOn;
        $this->endsOn = $endsOn;
        $this->finalizedAt = $finalizedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
        $this->deletedAt = $deletedAt;
    }

    public static function open(
        UuidInterface $careerId,
        ManagedClub $managedClub,
        ManagedLeague $managedLeague,
        SeasonLabel $label,
        CalendarDate $startsOn,
        CalendarDate $endsOn,
        DateTimeImmutable $occurredAt,
    ): self {
        return new self(
            Uuid::uuid7($occurredAt),
            $careerId,
            $managedClub,
            $managedLeague,
            $label,
            $startsOn,
            $endsOn,
            null,
            $occurredAt,
            $occurredAt,
            false,
            null,
        );
    }

    public static function reconstitute(
        UuidInterface $id,
        UuidInterface $careerId,
        ManagedClub $managedClub,
        ManagedLeague $managedLeague,
        SeasonLabel $label,
        CalendarDate $startsOn,
        CalendarDate $endsOn,
        ?DateTimeImmutable $finalizedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        bool $isDeleted,
        ?DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $careerId,
            $managedClub,
            $managedLeague,
            $label,
            $startsOn,
            $endsOn,
            $finalizedAt,
            $createdAt,
            $updatedAt,
            $isDeleted,
            $deletedAt,
        );
    }

    public function finalize(DateTimeImmutable $occurredAt): void
    {
        if ($this->finalizedAt !== null) {
            throw new DomainException('Cannot finalize an already finalized season.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Finalized-at timestamp cannot precede updated-at timestamp.');
        }

        $this->finalizedAt = $occurredAt;
        $this->updatedAt = $occurredAt;
    }

    public function delete(DateTimeImmutable $occurredAt): void
    {
        if ($this->isDeleted) {
            throw new DomainException('Cannot delete an already deleted season.');
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
            throw new DomainException('Cannot restore an active season.');
        }

        UtcInstant::assert($occurredAt);

        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Restored-at timestamp cannot precede updated-at timestamp.');
        }

        $this->isDeleted = false;
        $this->updatedAt = $occurredAt;
    }
}
