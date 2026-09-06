<?php

declare(strict_types=1);

namespace App\Domain\Season;

use DomainException;
use Ramsey\Uuid\UuidInterface;

/** The club snapshot selected for a single season. */
final readonly class ManagedClub
{
    public ?UuidInterface $id;
    public string $name;

    public function __construct(?UuidInterface $id, string $name)
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 120) {
            throw new DomainException('Managed club name must contain between 1 and 120 characters.');
        }

        $this->id = $id;
        $this->name = $name;
    }
}
