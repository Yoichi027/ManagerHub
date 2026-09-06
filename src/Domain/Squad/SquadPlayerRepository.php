<?php

declare(strict_types=1);

namespace App\Domain\Squad;

use Ramsey\Uuid\UuidInterface;

interface SquadPlayerRepository
{
    /** @return list<SquadPlayer> */
    public function findBySeasonId(UuidInterface $seasonId): array;
    public function findById(UuidInterface $id): ?SquadPlayer;

    public function add(SquadPlayer $player): void;
    public function save(SquadPlayer $player): void;
}
