<?php

declare(strict_types=1);

namespace App\Domain\Career;

use DomainException;

final readonly class GameEdition
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > 32) {
            throw new DomainException('Game edition must contain between 1 and 32 characters.');
        }

        $this->value = $value;
    }
}
