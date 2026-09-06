<?php

declare(strict_types=1);

namespace App\Application\Squad\Dashboard;

use App\Domain\Squad\SquadPlayer;
use App\Domain\Squad\SquadStatus;

final readonly class SquadOverview
{
    /** @param list<SquadPlayer> $players */
    private function __construct(
        public int $totalPlayers,
        public int $starters,
        public int $substitutes,
        public int $loanedOut,
        public ?float $averageOverall,
        public int $totalValueInCents,
        public ?SquadPlayer $mostValuablePlayer,
        public ?SquadPlayer $highestPotentialPlayer,
        public ?SquadPlayer $highestOverallPlayer,
    ) {}

    /** @param list<SquadPlayer> $players */
    public static function fromPlayers(array $players): self
    {
        $starters = 0;
        $substitutes = 0;
        $loanedOut = 0;
        $overallTotal = 0;
        $totalValueInCents = 0;
        $mostValuablePlayer = null;
        $highestPotentialPlayer = null;
        $highestOverallPlayer = null;

        foreach ($players as $player) {
            match ($player->status) {
                SquadStatus::Starter => $starters++,
                SquadStatus::Substitute => $substitutes++,
                SquadStatus::LoanedOut => $loanedOut++,
                SquadStatus::Active => null,
            };

            $overall = $player->overallFinal ?? $player->overallInitial;
            $potential = $player->potentialFinal ?? $player->potentialInitial;
            $valueInCents = self::toCents($player->valueFinal ?? $player->valueInitial);
            $overallTotal += $overall;
            $totalValueInCents += $valueInCents;

            if ($mostValuablePlayer === null || $valueInCents > self::toCents($mostValuablePlayer->valueFinal ?? $mostValuablePlayer->valueInitial)) {
                $mostValuablePlayer = $player;
            }
            if ($highestPotentialPlayer === null || $potential > ($highestPotentialPlayer->potentialFinal ?? $highestPotentialPlayer->potentialInitial)) {
                $highestPotentialPlayer = $player;
            }
            if ($highestOverallPlayer === null || $overall > ($highestOverallPlayer->overallFinal ?? $highestOverallPlayer->overallInitial)) {
                $highestOverallPlayer = $player;
            }
        }

        return new self(
            count($players),
            $starters,
            $substitutes,
            $loanedOut,
            $players === [] ? null : $overallTotal / count($players),
            $totalValueInCents,
            $mostValuablePlayer,
            $highestPotentialPlayer,
            $highestOverallPlayer,
        );
    }

    private static function toCents(string $value): int
    {
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
