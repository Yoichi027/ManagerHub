<?php

declare(strict_types=1);

namespace App\Tests\Web\Identity;

use App\Tests\Support\WebTester;
use LogicException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\Runner\Console\ConsoleApplicationRunner;

final class RegisterCest
{
    /** @var list<string> */
    private array $createdUsernames = [];

    public function _after(): void
    {
        if ($this->createdUsernames === []) {
            return;
        }

        $runner = new ConsoleApplicationRunner(dirname(__DIR__, 3), environment: 'test');
        $connection = $runner->getContainer()->get(ConnectionInterface::class);

        if ($connection->createCommand('SELECT DATABASE()')->queryScalar() !== 'manager_hub_test') {
            throw new LogicException('Web tests must use the manager_hub_test database.');
        }

        foreach ($this->createdUsernames as $username) {
            $connection->createCommand()->delete('users', ['username' => $username])->execute();
        }

        $connection->close();
    }

    public function registrationPageShowsTheForm(WebTester $I): void
    {
        $I->amOnPage('/register');

        $I->seeResponseCodeIs(200);
        $I->see('Create your account', 'h1');
        $I->seeElement('form#register-form');
        $I->seeElement('input', ['name' => '_csrf']);
    }

    public function invalidSubmissionShowsFieldErrors(WebTester $I): void
    {
        $I->amOnPage('/register');
        $I->submitForm('#register-form', [
            'username' => '',
            'email' => '',
            'password' => '',
        ]);

        $I->seeResponseCodeIs(422);
        $I->see('Username is required.');
        $I->see('Email is required.');
        $I->see('Password is required.');
    }

    public function availableDetailsCreateAnAccount(WebTester $I): void
    {
        $details = $this->details();

        $I->amOnPage('/register');
        $I->submitForm('#register-form', $details);

        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl('/');
        $I->see('Plan every season with confidence.', 'h1');
    }

    public function duplicateUsernameShowsAnError(WebTester $I): void
    {
        $details = $this->details();

        $I->amOnPage('/register');
        $I->submitForm('#register-form', $details);
        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl('/');

        $I->amOnPage('/register');
        $I->submitForm('#register-form', [
            'username' => $details['username'],
            'email' => 'other' . bin2hex(random_bytes(5)) . '@example.com',
            'password' => $details['password'],
        ]);

        $I->seeResponseCodeIs(422);
        $I->see('Username is already in use.');
    }

    public function registeredUserCanLogIn(WebTester $I): void
    {
        $details = $this->details();

        $I->amOnPage('/register');
        $I->submitForm('#register-form', $details);
        $I->seeResponseCodeIs(200);

        $I->amOnPage('/login');
        $I->submitForm('#login-form', [
            'username' => $details['username'],
            'password' => $details['password'],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl('/');
        $I->see('Signed in');
    }

    public function invalidLoginShowsAGenericError(WebTester $I): void
    {
        $I->amOnPage('/login');
        $I->submitForm('#login-form', [
            'username' => 'UnknownUser',
            'password' => 'Password1!',
        ]);

        $I->seeResponseCodeIs(422);
        $I->see('Username or password is incorrect.');
    }

    /** @return array{username: string, email: string, password: string} */
    private function details(): array
    {
        $suffix = bin2hex(random_bytes(6));
        $username = 'Test' . $suffix;
        $this->createdUsernames[] = $username;

        return [
            'username' => $username,
            'email' => 'test' . $suffix . '@example.com',
            'password' => 'Correct horse 7! battery',
        ];
    }
}
