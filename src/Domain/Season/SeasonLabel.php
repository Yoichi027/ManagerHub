<?php

declare(strict_types=1);

namespace App\Domain\Season;

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
}
