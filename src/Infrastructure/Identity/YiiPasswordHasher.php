<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\PasswordHasher;
use App\Domain\Identity\PasswordHash;
use SensitiveParameter;
use Yiisoft\Security\PasswordHasher as YiiPasswordHasherService;

final readonly class YiiPasswordHasher implements PasswordHasher
{
    public function __construct(private YiiPasswordHasherService $hasher) {}

    public function hash(#[SensitiveParameter] string $password): PasswordHash
    {
        return new PasswordHash($this->hasher->hash($password));
    }

    public function verify(#[SensitiveParameter] string $password, PasswordHash $hash): bool
    {
        return $this->hasher->validate($password, $hash->value);
    }
}
