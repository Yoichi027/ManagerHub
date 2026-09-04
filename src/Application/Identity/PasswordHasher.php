<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PasswordHash;

interface PasswordHasher
{
    public function hash(string $password): PasswordHash;
}
