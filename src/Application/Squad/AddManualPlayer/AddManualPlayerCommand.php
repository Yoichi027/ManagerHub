<?php

declare(strict_types=1);

namespace App\Application\Squad\AddManualPlayer;

final readonly class AddManualPlayerCommand
{
    public function __construct(
        public string $careerId,
        public string $userId,
        public string $name,
        public string $birthDate,
        public string $nationalityCode,
        public string $position,
        public int $overall,
        public int $potential,
        public string $value,
        public string $status = 'Starter',
    ) {}
}
