<?php

declare(strict_types=1);

namespace App\Domain\Shared\Time;

use DateTimeImmutable;
use DomainException;

final class UtcInstant
{
    public static function assert(?DateTimeImmutable ...$instants): void
    {
        foreach ($instants as $instant) {
            if ($instant !== null && $instant->getTimezone()->getName() !== 'UTC') {
                throw new DomainException('Instants must use the UTC timezone.');
            }
        }
    }
}
