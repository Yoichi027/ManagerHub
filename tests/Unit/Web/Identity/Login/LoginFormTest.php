<?php

declare(strict_types=1);

namespace App\Tests\Unit\Web\Identity\Login;

use App\Web\Identity\Login\LoginForm;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class LoginFormTest extends Unit
{
    public function testRequiresUsernameAndPassword(): void
    {
        $form = new LoginForm();

        assertSame(false, $form->isValid());
        assertSame(['Username or email is required.'], $form->errorsFor('identifier'));
        assertSame(['Password is required.'], $form->errorsFor('password'));
    }

    public function testPreservesTheSubmittedIdentifierAndAddsAGenericCredentialError(): void
    {
        $form = LoginForm::fromInput(['identifier' => ' Tiago42 ', 'password' => 'Password1!']);

        assertTrue($form->isValid());
        assertSame(' Tiago42 ', $form->identifier);
        $form->addInvalidCredentialsError();
        assertSame(['Username, email or password is incorrect.'], $form->errorsFor('general'));
    }
}
