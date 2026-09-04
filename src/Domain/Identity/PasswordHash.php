<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use DomainException;

final readonly class PasswordHash
{
    public string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw new DomainException('Password hash cannot be empty.');
        }

        if (strlen($value) > 255) {
            throw new DomainException('Password hash cannot exceed 255 bytes.');
        }

        $this->value = $value;
    }
}
