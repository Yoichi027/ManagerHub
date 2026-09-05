<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangePassword;

use Ramsey\Uuid\UuidInterface;

final readonly class ChangePasswordCommand
{
    public function __construct(
        public UuidInterface $userId,
        public string $currentPassword,
        public string $newPassword,
    ) {}
}
