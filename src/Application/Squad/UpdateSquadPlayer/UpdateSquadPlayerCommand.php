<?php
declare(strict_types=1);
namespace App\Application\Squad\UpdateSquadPlayer;
final readonly class UpdateSquadPlayerCommand
{
    public function __construct(public string $careerId, public string $userId, public string $squadPlayerId, public string $name, public string $birthDate, public string $nationalityCode, public string $position, public int $overallInitial, public int $potentialInitial, public string $valueInitial, public ?int $overallCurrent, public ?int $potentialCurrent, public ?string $valueCurrent, public string $status) {}
}
