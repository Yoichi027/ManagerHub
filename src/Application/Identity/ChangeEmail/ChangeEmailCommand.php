<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangeEmail;

use Ramsey\Uuid\UuidInterface;

final readonly class ChangeEmailCommand
{
    public function __construct(
        public UuidInterface $userId,
        public string $email,
    ) {}
}
