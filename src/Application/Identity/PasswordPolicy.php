<?php

declare(strict_types=1);

namespace App\Application\Identity;

final class PasswordPolicy
{
    public function isValid(string $password): bool
    {
        $length = mb_strlen($password, 'UTF-8');

        return $length >= 8
            && $length <= 128
            && preg_match('/\p{Lu}/u', $password) === 1
            && preg_match('/\p{N}/u', $password) === 1
            && preg_match('/[\p{P}\p{S}]/u', $password) === 1;
    }
}
