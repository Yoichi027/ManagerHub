<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Identity;

use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use DomainException;

use function PHPUnit\Framework\assertSame;

final class UsernameTest extends Unit
{
    public function testTrimsWhitespaceAndPreservesCasing(): void
    {
        $username = new Username('  Tiago42  ');

        assertSame('Tiago42', $username->value);
    }

    public function testRejectsAnEmptyValue(): void
    {
        $this->expectException(DomainException::class);

        new Username('   ');
    }

    public function testRejectsAValueLongerThanTwentyCharacters(): void
    {
        $this->expectException(DomainException::class);

        new Username(str_repeat('a', 21));
    }

    public function testRejectsCharactersOutsideAsciiLettersAndDigits(): void
    {
        $this->expectException(DomainException::class);

        new Username('tiago_silva');
    }
}
