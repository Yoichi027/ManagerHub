<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use Ramsey\Uuid\UuidInterface;

interface UserRepository
{
    public function existsByUsername(Username $username): bool;

    public function existsByEmail(Email $email): bool;

    public function findByUsername(Username $username): ?User;

    public function findByEmail(Email $email): ?User;

    public function findByUsernameIncludingDeleted(Username $username): ?User;

    public function findByEmailIncludingDeleted(Email $email): ?User;

    public function findById(UuidInterface $id): ?User;

    public function add(User $user): void;

    public function save(User $user): void;
}
