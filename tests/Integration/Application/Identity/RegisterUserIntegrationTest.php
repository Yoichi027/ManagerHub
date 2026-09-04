<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Identity;

use App\Application\Identity\RegisterUser\RegisterUser;
use App\Application\Identity\RegisterUser\RegisterUserCommand;
use App\Application\Identity\RegisterUser\RegisterUserResult;
use Codeception\Test\Unit;
use LogicException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\Runner\Console\ConsoleApplicationRunner;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringStartsWith;
use function PHPUnit\Framework\assertTrue;

final class RegisterUserIntegrationTest extends Unit
{
    private ConnectionInterface $connection;
    private RegisterUser $registerUser;
    private array $createdUsernames = [];

    protected function _before(): void
    {
        $runner = new ConsoleApplicationRunner(dirname(__DIR__, 4), environment: 'test');
        $container = $runner->getContainer();

        $this->connection = $container->get(ConnectionInterface::class);
        $this->registerUser = $container->get(RegisterUser::class);

        if ($this->connection->createCommand('SELECT DATABASE()')->queryScalar() !== 'manager_hub_test') {
            throw new LogicException('Integration tests must use the manager_hub_test database.');
        }
    }

    protected function _after(): void
    {
        foreach ($this->createdUsernames as $username) {
            $this->connection
                ->createCommand()
                ->delete('users', ['username' => $username])
                ->execute();
        }

        $this->connection->close();
    }

    public function testRegistersAUserWithAnArgon2idPasswordHash(): void
    {
        $suffix = bin2hex(random_bytes(6));
        $username = 'Test' . $suffix;
        $email = 'test' . $suffix . '@example.com';
        $password = 'Correct horse 7! battery';
        $this->createdUsernames[] = $username;

        $result = $this->registerUser->register(new RegisterUserCommand($username, $email, $password));
        $row = $this->connection
            ->select(['username', 'email', 'password_hash', 'is_deleted'])
            ->from('users')
            ->where(['username' => $username])
            ->one();

        assertSame(RegisterUserResult::Registered, $result);
        assertSame($username, $row['username']);
        assertSame($email, $row['email']);
        assertStringStartsWith('$argon2id$', $row['password_hash']);
        assertTrue(password_verify($password, $row['password_hash']));
        assertSame(0, (int) $row['is_deleted']);
    }
}
