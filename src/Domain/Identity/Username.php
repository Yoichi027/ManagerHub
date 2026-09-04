<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use DomainException;

final readonly class Username
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new DomainException('Username cannot be empty.');
        }

        if (strlen($value) > 20) {
            throw new DomainException('Username cannot exceed 20 characters.');
        }

        if (preg_match('/^[A-Za-z0-9]+$/', $value) !== 1) {
            throw new DomainException('Username may only contain ASCII letters and digits.');
        }

        $this->value = $value;
    }
}
