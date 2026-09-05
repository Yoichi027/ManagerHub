<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\RegisterUser\RegisterUserResult;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertSame;

final class RegisterUserResultTest extends Unit
{
    public function testDefinesEveryRegistrationOutcome(): void
    {
        assertSame(
            [
                RegisterUserResult::Registered,
                RegisterUserResult::UsernameTaken,
                RegisterUserResult::EmailTaken,
                RegisterUserResult::DeactivatedAccount,
                RegisterUserResult::InvalidInput,
            ],
            RegisterUserResult::cases(),
        );
    }
}
