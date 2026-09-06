<?php
declare(strict_types=1);
namespace App\Application\Squad\UpdateSquadPlayer;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Career\CareerRepository;
use App\Domain\Season\SeasonRepository;
use App\Domain\Shared\CalendarDate;
use App\Domain\Squad\Position;
use App\Domain\Squad\SquadPlayerRepository;
use App\Domain\Squad\SquadStatus;
use Ramsey\Uuid\Uuid;
final readonly class UpdateSquadPlayer
{
    public function __construct(private CareerRepository $careers, private SeasonRepository $seasons, private SquadPlayerRepository $players, private UtcClock $clock) {}
    public function update(UpdateSquadPlayerCommand $command): UpdateSquadPlayerResult
    {
        try { $careerId = Uuid::fromString($command->careerId); $userId = Uuid::fromString($command->userId); $playerId = Uuid::fromString($command->squadPlayerId); $birthDate = new CalendarDate($command->birthDate); $position = new Position($command->position); $status = SquadStatus::from($command->status); } catch (\Throwable) { return UpdateSquadPlayerResult::InvalidInput; }
        $career = $this->careers->findById($careerId); $player = $this->players->findById($playerId);
        $season = $player === null ? null : $this->seasons->findById($player->seasonId);
        if ($career === null || $player === null || $season === null || !$season->careerId->equals($careerId)) { return UpdateSquadPlayerResult::NotFound; }
        if (!$career->userId->equals($userId)) { return UpdateSquadPlayerResult::NotOwner; }
        try { $player->revise($command->name, $birthDate, $command->nationalityCode, $position, $command->overallInitial, $command->potentialInitial, $command->valueInitial, $command->overallCurrent, $command->potentialCurrent, $command->valueCurrent, $status, $this->clock->now()); } catch (\Throwable) { return UpdateSquadPlayerResult::InvalidInput; }
        $this->players->save($player);
        return UpdateSquadPlayerResult::Updated;
    }
}
