<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Identity;

use App\Domain\Identity\PasswordHash;
use Codeception\Test\Unit;
use DomainException;

use function PHPUnit\Framework\assertSame;

final class PasswordHashTest extends Unit
{
    public function testPreservesAnOpaqueHashValue(): void
    {
        $hash = new PasswordHash('$argon2id$v=19$m=65536,t=4,p=1$example$hash');

        assertSame('$argon2id$v=19$m=65536,t=4,p=1$example$hash', $hash->value);
    }

    public function testRejectsAnEmptyValue(): void
    {
        $this->expectException(DomainException::class);

        new PasswordHash('');
    }

    public function testRejectsAValueLongerThanTwoHundredAndFiftyFiveBytes(): void
    {
        $this->expectException(DomainException::class);

        new PasswordHash(str_repeat('a', 256));
    }
}
