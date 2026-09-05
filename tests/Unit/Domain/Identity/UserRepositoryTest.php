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
        $findByUsername = new ReflectionMethod(UserRepository::class, 'findByUsername');
        $findByEmail = new ReflectionMethod(UserRepository::class, 'findByEmail');
        $findById = new ReflectionMethod(UserRepository::class, 'findById');
        $add = new ReflectionMethod(UserRepository::class, 'add');
        $save = new ReflectionMethod(UserRepository::class, 'save');

        assertSame(Username::class, $usernameExists->getParameters()[0]->getType()?->getName());
        assertSame('bool', $usernameExists->getReturnType()?->getName());
        assertSame(Email::class, $emailExists->getParameters()[0]->getType()?->getName());
        assertSame('bool', $emailExists->getReturnType()?->getName());
        assertSame(Username::class, $findByUsername->getParameters()[0]->getType()?->getName());
        assertSame(User::class, $findByUsername->getReturnType()?->getName());
        assertSame(Email::class, $findByEmail->getParameters()[0]->getType()?->getName());
        assertSame(User::class, $findByEmail->getReturnType()?->getName());
        assertSame('Ramsey\\Uuid\\UuidInterface', $findById->getParameters()[0]->getType()?->getName());
        assertSame(User::class, $findById->getReturnType()?->getName());
        assertSame(User::class, $add->getParameters()[0]->getType()?->getName());
        assertSame('void', $add->getReturnType()?->getName());
        assertSame(User::class, $save->getParameters()[0]->getType()?->getName());
        assertSame('void', $save->getReturnType()?->getName());
    }
}
