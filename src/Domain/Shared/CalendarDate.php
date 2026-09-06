<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;

/** A timezone-free calendar date stored in ISO-8601 format. */
final readonly class CalendarDate
{
    public string $value;

    public function __construct(string $value)
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value
        ) {
            throw new DomainException('Calendar dates must use a valid YYYY-MM-DD value.');
        }

        $this->value = $value;
    }

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }
}
