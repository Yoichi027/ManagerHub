<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Identity;

use App\Domain\Identity\Email;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use App\Domain\Identity\Username;
use Codeception\Test\Unit;
use ReflectionMethod;

use function PHPUnit\Framework\assertSame;

final class UserRepositoryTest extends Unit
{
    public function testDefinesTheRegistrationPersistenceContract(): void
    {
        $usernameExists = new ReflectionMethod(UserRepository::class, 'existsByUsername');
        $emailExists = new ReflectionMethod(UserRepository::class, 'existsByEmail');
        $add = new ReflectionMethod(UserRepository::class, 'add');

        assertSame(Username::class, $usernameExists->getParameters()[0]->getType()?->getName());
        assertSame('bool', $usernameExists->getReturnType()?->getName());
        assertSame(Email::class, $emailExists->getParameters()[0]->getType()?->getName());
        assertSame('bool', $emailExists->getReturnType()?->getName());
        assertSame(User::class, $add->getParameters()[0]->getType()?->getName());
        assertSame('void', $add->getReturnType()?->getName());
    }
}
