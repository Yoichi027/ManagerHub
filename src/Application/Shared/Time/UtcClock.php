<?php

declare(strict_types=1);

namespace App\Application\Shared\Time;

use DateTimeImmutable;

interface UtcClock
{
    public function now(): DateTimeImmutable;
}
