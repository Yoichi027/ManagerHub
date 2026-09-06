<?php
declare(strict_types=1);
namespace App\Application\Squad\DeleteSquadPlayer;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Career\CareerRepository;
use App\Domain\Season\SeasonRepository;
use App\Domain\Squad\SquadPlayerRepository;
use Ramsey\Uuid\Uuid;
final readonly class DeleteSquadPlayer
{
    public function __construct(private CareerRepository $careers, private SeasonRepository $seasons, private SquadPlayerRepository $players, private UtcClock $clock) {}
    public function delete(DeleteSquadPlayerCommand $command): DeleteSquadPlayerResult
    {
        try { $careerId = Uuid::fromString($command->careerId); $userId = Uuid::fromString($command->userId); $playerId = Uuid::fromString($command->squadPlayerId); } catch (\Throwable) { return DeleteSquadPlayerResult::InvalidInput; }
        $career = $this->careers->findById($careerId); $player = $this->players->findById($playerId); $season = $player === null ? null : $this->seasons->findById($player->seasonId);
        if ($career === null || $player === null || $season === null || !$season->careerId->equals($careerId)) return DeleteSquadPlayerResult::NotFound;
        if (!$career->userId->equals($userId)) return DeleteSquadPlayerResult::NotOwner;
        try { $player->delete($this->clock->now()); } catch (\Throwable) { return DeleteSquadPlayerResult::InvalidInput; }
        $this->players->save($player);
        return DeleteSquadPlayerResult::Deleted;
    }
}
