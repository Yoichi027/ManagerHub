<?php

declare(strict_types=1);

namespace App\Domain\Career;

use DomainException;

final readonly class ManagerName
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 100) {
            throw new DomainException('Manager name must contain between 1 and 100 characters.');
        }
        $this->value = $value;
    }
}
