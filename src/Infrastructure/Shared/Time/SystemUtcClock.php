<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Time;

use App\Application\Shared\Time\UtcClock;
use DateTimeImmutable;
use DateTimeZone;

final class SystemUtcClock implements UtcClock
{
    /**
     * @throws \DateMalformedStringException
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
