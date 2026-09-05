<?php

declare(strict_types=1);

namespace App\Application\Identity\DeactivateAccount;

use Ramsey\Uuid\UuidInterface;

final readonly class DeactivateAccountCommand
{
    public function __construct(public UuidInterface $userId, public string $password) {}
}
