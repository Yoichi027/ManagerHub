<?php

declare(strict_types=1);

namespace App\Domain\Season;

use App\Domain\Shared\CalendarDate;
use DomainException;

final readonly class SeasonLabel
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > 20) {
            throw new DomainException('Season label must contain between 1 and 20 characters.');
        }

        $this->value = $value;
    }

    public static function fromPeriod(CalendarDate $startsOn, CalendarDate $endsOn): self
    {
        $startYear = (int) substr($startsOn->value, 0, 4);
        $endYear = (int) substr($endsOn->value, 0, 4);

        return new self($startYear === $endYear
            ? (string) $startYear
            : sprintf('%d/%02d', $startYear, $endYear % 100));
    }
}
