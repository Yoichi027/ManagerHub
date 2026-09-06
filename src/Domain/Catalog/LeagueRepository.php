<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use Ramsey\Uuid\UuidInterface;

interface LeagueRepository
{
    public function findById(UuidInterface $id): ?League;

    /** @return list<League> */
    public function all(): array;
}
