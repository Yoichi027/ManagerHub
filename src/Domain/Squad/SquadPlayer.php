<?php

declare(strict_types=1);

namespace App\Domain\Squad;

use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SquadPlayer
{
    public readonly UuidInterface $id;
    public function __construct(UuidInterface $id, public readonly UuidInterface $seasonId, public readonly UuidInterface $playerId, public readonly string $playerName, public readonly Position $position, public readonly int $overallInitial, public readonly int $potentialInitial, public readonly string $valueInitial, public readonly SquadStatus $status, public readonly DateTimeImmutable $createdAt, public readonly DateTimeImmutable $updatedAt, public readonly bool $isDeleted, public readonly ?DateTimeImmutable $deletedAt)
    {
        foreach ([$overallInitial,$potentialInitial] as $rating) {
            if ($rating < 1 || $rating > 99) {
                throw new DomainException('Ratings must be between 1 and 99.');
            }
        } UtcInstant::assert($createdAt, $updatedAt, $deletedAt);
    } public static function add(Player $player, UuidInterface $season, Position $position, int $overall, int $potential, string $value, DateTimeImmutable $at): self
    {
        return new self(Uuid::uuid7($at), $season, $player->id, $player->name, $position, $overall, $potential, $value, SquadStatus::Active, $at, $at, false, null);
    }
}
