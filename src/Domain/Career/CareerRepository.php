<?php

declare(strict_types=1);

namespace App\Domain\Career;

use Ramsey\Uuid\UuidInterface;

interface CareerRepository
{
    public function findById(UuidInterface $id): ?Career;

    /** @return list<Career> */
    public function findByUserId(UuidInterface $userId): array;

    public function add(Career $career): void;

    public function save(Career $career): void;
}
