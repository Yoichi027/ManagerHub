<?php

declare(strict_types=1);

namespace App\Domain\Career;

use DomainException;

final readonly class CareerName
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new DomainException('Career name cannot be empty.');
        }

        if (mb_strlen($value) > 100) {
            throw new DomainException('Career name cannot exceed 100 characters.');
        }

        $this->value = $value;
    }
}
