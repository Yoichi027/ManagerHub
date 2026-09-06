<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use Ramsey\Uuid\UuidInterface;

interface ClubRepository
{
    public function findById(UuidInterface $id): ?Club;

    /** @return list<Club> */
    public function all(): array;
}
