<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Identity;

use App\Application\Identity\PasswordHasher;
use App\Domain\Identity\PasswordHash;
use Codeception\Test\Unit;
use ReflectionMethod;

use function PHPUnit\Framework\assertSame;

final class PasswordHasherTest extends Unit
{
    public function testDefinesThePasswordHashingContract(): void
    {
        $method = new ReflectionMethod(PasswordHasher::class, 'hash');

        assertSame('string', $method->getParameters()[0]->getType()?->getName());
        assertSame(PasswordHash::class, $method->getReturnType()?->getName());

        $verify = new ReflectionMethod(PasswordHasher::class, 'verify');
        assertSame('string', $verify->getParameters()[0]->getType()?->getName());
        assertSame(PasswordHash::class, $verify->getParameters()[1]->getType()?->getName());
        assertSame('bool', $verify->getReturnType()?->getName());
    }
}
