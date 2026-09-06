<?php

declare(strict_types=1);

namespace App\Domain\Season;

use Ramsey\Uuid\UuidInterface;

interface SeasonRepository
{
    public function findById(UuidInterface $id): ?Season;

    public function findActiveByCareerId(UuidInterface $careerId): ?Season;

    public function add(Season $season): void;

    public function save(Season $season): void;
}
