<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Squad\Dashboard;

use App\Application\Squad\Dashboard\SquadOverview;
use App\Domain\Shared\CalendarDate;
use App\Domain\Squad\Player;
use App\Domain\Squad\Position;
use App\Domain\Squad\SquadPlayer;
use App\Domain\Squad\SquadStatus;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

final class SquadOverviewTest extends Unit
{
    public function testBuildsCurrentSquadMetricsAndHighlights(): void
    {
        $players = [
            $this->player('Marta', 80, 88, '1000000.00', SquadStatus::Starter, 82, 90, '1500000.50'),
            $this->player('Rui', 85, 86, '2000000.00', SquadStatus::Substitute),
            $this->player('Ines', 75, 92, '2500000.00', SquadStatus::LoanedOut),
            $this->player('Joao', 70, 78, '0', SquadStatus::Active),
        ];

        $overview = SquadOverview::fromPlayers($players);

        self::assertSame(4, $overview->totalPlayers);
        self::assertSame(1, $overview->starters);
        self::assertSame(1, $overview->substitutes);
        self::assertSame(1, $overview->loanedOut);
        self::assertSame(78.0, $overview->averageOverall);
        self::assertSame(600000050, $overview->totalValueInCents);
        self::assertSame('Ines', $overview->mostValuablePlayer?->playerName);
        self::assertSame('Ines', $overview->highestPotentialPlayer?->playerName);
        self::assertSame('Rui', $overview->highestOverallPlayer?->playerName);
    }

    public function testHandlesAnEmptySquad(): void
    {
        $overview = SquadOverview::fromPlayers([]);

        self::assertSame(0, $overview->totalPlayers);
        self::assertNull($overview->averageOverall);
        self::assertSame(0, $overview->totalValueInCents);
        self::assertNull($overview->mostValuablePlayer);
    }

    private function player(string $name, int $overall, int $potential, string $value, SquadStatus $status, ?int $currentOverall = null, ?int $currentPotential = null, ?string $currentValue = null): SquadPlayer
    {
        $at = new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC'));
        $player = SquadPlayer::add(Player::create(Uuid::uuid7($at), $name, new CalendarDate('2000-01-01'), 'PT', $at), Uuid::uuid7($at), new Position('CM'), $overall, $potential, $value, $status, $at);
        $player->revise($name, new CalendarDate('2000-01-01'), 'PT', new Position('CM'), $overall, $potential, $value, $currentOverall, $currentPotential, $currentValue, $status, $at);
        return $player;
    }
}
