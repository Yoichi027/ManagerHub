<?php

declare(strict_types=1);

namespace App\Domain\Identity;

interface UserRepository
{
    public function existsByUsername(Username $username): bool;

    public function existsByEmail(Email $email): bool;

    public function add(User $user): void;
}
