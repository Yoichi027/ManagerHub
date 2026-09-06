<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use Ramsey\Uuid\UuidInterface;

final readonly class League
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
        public string $country,
        public ?string $logoUrl,
        public int $seasonStartsMonth,
        public int $seasonStartsDay,
        public int $seasonEndsMonth,
        public int $seasonEndsDay,
    ) {}
}
