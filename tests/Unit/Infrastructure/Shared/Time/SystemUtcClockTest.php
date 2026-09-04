<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Shared\Time;

use App\Infrastructure\Shared\Time\SystemUtcClock;
use Codeception\Test\Unit;

use function PHPUnit\Framework\assertSame;

final class SystemUtcClockTest extends Unit
{
    public function testReturnsAnInstantInUtc(): void
    {
        $instant = (new SystemUtcClock())->now();

        assertSame('UTC', $instant->getTimezone()->getName());
    }
}
