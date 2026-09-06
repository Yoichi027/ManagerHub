<?php

declare(strict_types=1);

namespace App\Domain\Squad;

use App\Domain\Shared\CalendarDate;
use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SquadPlayer
{
    public readonly UuidInterface $id;
    public readonly UuidInterface $seasonId;
    public readonly UuidInterface $playerId;
    public private(set) string $playerName;
    public private(set) CalendarDate $birthDate;
    public private(set) string $nationalityCode;
    public private(set) Position $position;
    public private(set) int $overallInitial;
    public private(set) int $potentialInitial;
    public private(set) string $valueInitial;
    public private(set) ?int $overallFinal;
    public private(set) ?int $potentialFinal;
    public private(set) ?string $valueFinal;
    public private(set) SquadStatus $status;
    public readonly DateTimeImmutable $createdAt;
    public private(set) DateTimeImmutable $updatedAt;
    public private(set) bool $isDeleted;
    public private(set) ?DateTimeImmutable $deletedAt;

    public function __construct(UuidInterface $id, UuidInterface $seasonId, UuidInterface $playerId, string $playerName, CalendarDate $birthDate, string $nationalityCode, Position $position, int $overallInitial, int $potentialInitial, string $valueInitial, ?int $overallFinal, ?int $potentialFinal, ?string $valueFinal, SquadStatus $status, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt, bool $isDeleted, ?DateTimeImmutable $deletedAt)
    {
        if (trim($playerName) === '' || mb_strlen($playerName) > 120) {
            throw new DomainException('Player name is invalid.');
        }
        if (!preg_match('/^[A-Z]{2}$/', $nationalityCode)) {
            throw new DomainException('Nationality must use ISO alpha-2.');
        }
        foreach ([$overallInitial, $potentialInitial, $overallFinal, $potentialFinal] as $rating) {
            if ($rating === null) {
                continue;
            }
            if ($rating < 1 || $rating > 99) {
                throw new DomainException('Ratings must be between 1 and 99.');
            }
        }
        foreach ([$valueInitial, $valueFinal] as $value) {
            if ($value !== null && !preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
                throw new DomainException('Player values must be non-negative decimal amounts.');
            }
        }
        UtcInstant::assert($createdAt, $updatedAt, $deletedAt);
        $this->id = $id;
        $this->seasonId = $seasonId; $this->playerId = $playerId; $this->playerName = trim($playerName); $this->birthDate = $birthDate;
        $this->nationalityCode = $nationalityCode; $this->position = $position; $this->overallInitial = $overallInitial; $this->potentialInitial = $potentialInitial;
        $this->valueInitial = $valueInitial; $this->overallFinal = $overallFinal; $this->potentialFinal = $potentialFinal; $this->valueFinal = $valueFinal;
        $this->status = $status; $this->createdAt = $createdAt; $this->updatedAt = $updatedAt; $this->isDeleted = $isDeleted; $this->deletedAt = $deletedAt;
    } public static function add(Player $player, UuidInterface $season, Position $position, int $overall, int $potential, string $value, SquadStatus $status, DateTimeImmutable $at): self
    {
        return new self(Uuid::uuid7($at), $season, $player->id, $player->name, $player->birthDate, $player->nationalityCode, $position, $overall, $potential, $value, null, null, null, $status, $at, $at, false, null);
    }

    public function revise(string $name, CalendarDate $birthDate, string $nationalityCode, Position $position, int $overallInitial, int $potentialInitial, string $valueInitial, ?int $overallCurrent, ?int $potentialCurrent, ?string $valueCurrent, SquadStatus $status, DateTimeImmutable $occurredAt): void
    {
        $revised = new self($this->id, $this->seasonId, $this->playerId, $name, $birthDate, $nationalityCode, $position, $overallInitial, $potentialInitial, $valueInitial, $overallCurrent, $potentialCurrent, $valueCurrent, $status, $this->createdAt, $occurredAt, $this->isDeleted, $this->deletedAt);
        $this->playerName = $revised->playerName; $this->birthDate = $birthDate; $this->nationalityCode = $nationalityCode; $this->position = $position;
        $this->overallInitial = $overallInitial; $this->potentialInitial = $potentialInitial; $this->valueInitial = $valueInitial;
        $this->overallFinal = $overallCurrent; $this->potentialFinal = $potentialCurrent; $this->valueFinal = $valueCurrent; $this->status = $status; $this->updatedAt = $occurredAt;
    }

    public function delete(DateTimeImmutable $occurredAt): void
    {
        if ($this->isDeleted) {
            throw new DomainException('Cannot delete an already deleted squad player.');
        }
        UtcInstant::assert($occurredAt);
        if ($occurredAt < $this->updatedAt) {
            throw new DomainException('Deleted-at timestamp cannot precede updated-at timestamp.');
        }
        $this->isDeleted = true;
        $this->deletedAt = $occurredAt;
        $this->updatedAt = $occurredAt;
    }
}
