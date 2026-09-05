<?php

declare(strict_types=1);

namespace App\Tests\Unit\Web\Identity\Register;

use App\Application\Identity\RegisterUser\RegisterUserResult;
use App\Web\Identity\Register\RegisterForm;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class RegisterFormTest extends Unit
{
    public function testCreatesAnEmptyFormForMissingOrInvalidInput(): void
    {
        $form = RegisterForm::fromInput(null);

        assertSame('', $form->username);
        assertSame('', $form->email);
        assertSame('', $form->password);
    }

    public function testRejectsMissingRequiredFields(): void
    {
        $form = new RegisterForm();

        assertSame(false, $form->isValid());
        assertSame(['Username is required.'], $form->errorsFor('username'));
        assertSame(['Email is required.'], $form->errorsFor('email'));
        assertSame(['Password is required.'], $form->errorsFor('password'));
    }

    public function testPreservesStringInputWithoutNormalizingIt(): void
    {
        $form = RegisterForm::fromInput([
            'username' => ' Tiago42 ',
            'email' => ' Tiago@Example.com ',
            'password' => ' Password1! ',
        ]);

        assertTrue($form->isValid());
        assertSame(' Tiago42 ', $form->username);
        assertSame(' Tiago@Example.com ', $form->email);
        assertSame(' Password1! ', $form->password);
    }

    public function testMapsApplicationOutcomesToPresentationErrors(): void
    {
        $form = new RegisterForm('Tiago42', 'tiago@example.com', 'Password1!');

        $form->addBusinessError(RegisterUserResult::UsernameTaken);
        $form->addBusinessError(RegisterUserResult::EmailTaken);
        $form->addBusinessError(RegisterUserResult::InvalidInput);

        assertSame(['Username is already in use.'], $form->errorsFor('username'));
        assertSame(['Email is already in use.'], $form->errorsFor('email'));
        assertSame(['Please check the registration details.'], $form->errorsFor('general'));
    }
}
