<?php

declare(strict_types=1);

namespace App\Domain\Season;

use DomainException;
use Ramsey\Uuid\UuidInterface;

/** The league snapshot selected for a single season. */
final readonly class ManagedLeague
{
    public ?UuidInterface $id;
    public string $name;

    public function __construct(?UuidInterface $id, string $name)
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 100) {
            throw new DomainException('Managed league name must contain between 1 and 100 characters.');
        }

        $this->id = $id;
        $this->name = $name;
    }
}
