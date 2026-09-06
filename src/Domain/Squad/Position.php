<?php

declare(strict_types=1);

namespace App\Domain\Squad;

use DomainException;

final readonly class Position
{
    public function __construct(public string $value)
    {
        if (!in_array($value, ['GK','LB','LWB','CB','RB','RWB','CDM','CM','CAM','LM','RM','LW','LF','CF','RF','RW','ST'], true)) {
            throw new DomainException('Unsupported player position.');
        }
    }
}
