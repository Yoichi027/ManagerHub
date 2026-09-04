<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Identity;

use App\Infrastructure\Identity\YiiPasswordHasher;
use Codeception\Test\Unit;
use Yiisoft\Security\PasswordHasher;

use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertTrue;

final class YiiPasswordHasherTest extends Unit
{
    public function testHashesAPasswordUsingYiiSecurity(): void
    {
        $hash = (new YiiPasswordHasher(new PasswordHasher()))->hash('secret-password');

        assertNotSame('secret-password', $hash->value);
        assertTrue(password_verify('secret-password', $hash->value));
    }
}
