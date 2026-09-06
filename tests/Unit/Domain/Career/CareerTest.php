<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Career;

use App\Domain\Career\Career;
use App\Domain\Career\CareerName;
use App\Domain\Career\GameEdition;
use App\Domain\Career\ManagerName;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class CareerTest extends Unit
{
    public function testStartCreatesAnActiveCareerWithUuidVersionSeven(): void
    {
        $occurredAt = $this->utc('2026-09-06 12:00:00');
        $userId = Uuid::uuid7($occurredAt);

        $career = Career::start(new CareerName('Porto rebuild'), new ManagerName('Tiago Silva'), new GameEdition('FC26'), $userId, $occurredAt);

        assertSame(7, $career->id->getVersion());
        assertSame($userId, $career->userId);
        assertSame('Porto rebuild', $career->name->value);
        assertSame('Tiago Silva', $career->managerName->value);
        assertFalse($career->isDeleted);
    }

    public function testDeleteThenRestorePreservesTheLastDeletionTimestamp(): void
    {
        $createdAt = $this->utc('2026-09-06 12:00:00');
        $deletedAt = $this->utc('2026-09-07 12:00:00');
        $restoredAt = $this->utc('2026-09-08 12:00:00');
        $career = Career::start(new CareerName('Porto rebuild'), new ManagerName('Tiago Silva'), new GameEdition('FC26'), Uuid::uuid7($createdAt), $createdAt);

        $career->delete($deletedAt);
        $career->restore($restoredAt);

        assertFalse($career->isDeleted);
        assertSame($deletedAt, $career->deletedAt);
        assertSame($restoredAt, $career->updatedAt);
        assertTrue($career->id->getVersion() === 7);
    }

    private function utc(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
