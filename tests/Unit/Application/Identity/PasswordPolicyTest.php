<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\PasswordPolicy;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class PasswordPolicyTest extends Unit
{
    public function testAcceptsUnicodeWhitespaceAndAValidPassword(): void
    {
        $policy = new PasswordPolicy();

        assertTrue($policy->isValid('Cavalo azul 7 🦀 seguro'));
    }

    public function testRejectsPasswordsShorterThanEightCharacters(): void
    {
        assertFalse((new PasswordPolicy())->isValid('A1!shrt'));
    }

    public function testRejectsPasswordsLongerThanOneHundredAndTwentyEightCharacters(): void
    {
        assertFalse((new PasswordPolicy())->isValid(str_repeat('a', 129)));
    }

    public function testRejectsAPasswordWithoutAnUppercaseCharacter(): void
    {
        assertFalse((new PasswordPolicy())->isValid('password7!'));
    }

    public function testRejectsAPasswordWithoutANumber(): void
    {
        assertFalse((new PasswordPolicy())->isValid('Password!'));
    }

    public function testRejectsAPasswordWithoutASymbolOrPunctuationCharacter(): void
    {
        assertFalse((new PasswordPolicy())->isValid('Password7'));
    }
}
