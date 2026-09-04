<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Time;

use App\Application\Shared\Time\UtcClock;
use Codeception\Test\Unit;
use DateTimeImmutable;
use ReflectionMethod;

use function PHPUnit\Framework\assertSame;

final class UtcClockTest extends Unit
{
    public function testDefinesTheUtcTimeContract(): void
    {
        $method = new ReflectionMethod(UtcClock::class, 'now');

        assertSame(DateTimeImmutable::class, $method->getReturnType()?->getName());
    }
}
