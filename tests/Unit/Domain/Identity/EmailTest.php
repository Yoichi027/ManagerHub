<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Identity;

use App\Domain\Identity\Email;
use Codeception\Test\Unit;
use DomainException;

use function PHPUnit\Framework\assertSame;

final class EmailTest extends Unit
{
    public function testTrimsAndNormalizesToLowercase(): void
    {
        $email = new Email('  Tiago.Silva@Example.COM  ');

        assertSame('tiago.silva@example.com', $email->value);
    }

    public function testRejectsAnEmptyValue(): void
    {
        $this->expectException(DomainException::class);

        new Email('   ');
    }

    public function testRejectsAnInvalidEmailAddress(): void
    {
        $this->expectException(DomainException::class);

        new Email('not-an-email-address');
    }

    public function testRejectsAValueLongerThanTwoHundredAndFiftyFiveBytes(): void
    {
        $this->expectException(DomainException::class);

        new Email(str_repeat('a', 244) . '@example.com');
    }
}
