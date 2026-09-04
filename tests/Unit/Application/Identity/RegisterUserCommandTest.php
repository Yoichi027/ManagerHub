<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\RegisterUser\RegisterUserCommand;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertSame;

final class RegisterUserCommandTest extends Unit
{
    public function testPreservesTheRawRegistrationInput(): void
    {
        $command = new RegisterUserCommand(' Tiago42 ', ' Tiago@Example.com ', 'secret-password');

        assertSame(' Tiago42 ', $command->username);
        assertSame(' Tiago@Example.com ', $command->email);
        assertSame('secret-password', $command->password);
    }
}
