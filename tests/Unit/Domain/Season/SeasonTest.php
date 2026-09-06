<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Season;

use App\Domain\Season\ManagedClub;
use App\Domain\Season\ManagedLeague;
use App\Domain\Season\Season;
use App\Domain\Season\SeasonLabel;
use App\Domain\Shared\CalendarDate;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Ramsey\Uuid\Uuid;

use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class SeasonTest extends Unit
{
    public function testOpenThenFinalizePreservesTheSeasonSnapshots(): void
    {
        $createdAt = $this->utc('2026-07-01 10:00:00');
        $finalizedAt = $this->utc('2027-06-30 20:00:00');
        $careerId = Uuid::uuid7($createdAt);
        $clubId = Uuid::uuid7($createdAt);
        $leagueId = Uuid::uuid7($createdAt);

        $season = Season::open(
            $careerId,
            new ManagedClub($clubId, 'Sporting CP'),
            new ManagedLeague($leagueId, 'Portugal Primeira Liga (1)'),
            new SeasonLabel('2026/27'),
            new CalendarDate('2026-07-01'),
            new CalendarDate('2027-06-30'),
            $createdAt,
        );

        assertSame(7, $season->id->getVersion());
        assertNull($season->finalizedAt);
        assertSame('Sporting CP', $season->managedClub->name);

        $season->finalize($finalizedAt);

        assertSame($finalizedAt, $season->finalizedAt);
        assertSame($finalizedAt, $season->updatedAt);
    }

    public function testOpenRejectsAnInvertedCalendarRange(): void
    {
        $occurredAt = $this->utc('2026-07-01 10:00:00');
        $careerId = Uuid::uuid7($occurredAt);

        $this->expectException(DomainException::class);

        Season::open(
            $careerId,
            new ManagedClub(null, 'Sporting CP'),
            new ManagedLeague(null, 'Portugal Primeira Liga (1)'),
            new SeasonLabel('2026/27'),
            new CalendarDate('2027-06-30'),
            new CalendarDate('2026-07-01'),
            $occurredAt,
        );
    }

    public function testDeleteThenRestorePreservesTheLastDeletionTimestamp(): void
    {
        $createdAt = $this->utc('2026-07-01 10:00:00');
        $deletedAt = $this->utc('2026-07-02 10:00:00');
        $restoredAt = $this->utc('2026-07-03 10:00:00');
        $season = Season::open(
            Uuid::uuid7($createdAt),
            new ManagedClub(null, 'Sporting CP'),
            new ManagedLeague(null, 'Portugal Primeira Liga (1)'),
            new SeasonLabel('2026/27'),
            new CalendarDate('2026-07-01'),
            new CalendarDate('2027-06-30'),
            $createdAt,
        );

        $season->delete($deletedAt);
        $season->restore($restoredAt);

        assertTrue(!$season->isDeleted);
        assertSame($deletedAt, $season->deletedAt);
        assertSame($restoredAt, $season->updatedAt);
    }

    private function utc(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
