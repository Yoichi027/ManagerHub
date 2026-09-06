<?php

declare(strict_types=1);

namespace App\Application\Squad\AddManualPlayer;

use App\Application\Shared\Time\UtcClock;
use App\Application\Shared\Transaction\TransactionManager;
use App\Domain\Career\CareerRepository;
use App\Domain\Season\SeasonRepository;
use App\Domain\Shared\CalendarDate;
use App\Domain\Squad\Player;
use App\Domain\Squad\PlayerRepository;
use App\Domain\Squad\Position;
use App\Domain\Squad\SquadPlayer;
use App\Domain\Squad\SquadPlayerRepository;
use App\Domain\Squad\SquadStatus;
use DomainException;
use Ramsey\Uuid\Uuid;

final readonly class AddManualPlayer
{
    public function __construct(
        private CareerRepository $careers,
        private SeasonRepository $seasons,
        private PlayerRepository $players,
        private SquadPlayerRepository $squadPlayers,
        private TransactionManager $transactions,
        private UtcClock $clock,
    ) {}

    public function add(AddManualPlayerCommand $command): AddManualPlayerResult
    {
        try {
            $careerId = Uuid::fromString($command->careerId);
            $userId = Uuid::fromString($command->userId);
            $birthDate = new CalendarDate($command->birthDate);
            $position = new Position($command->position);
            $status = SquadStatus::from($command->status);
        } catch (\Throwable) {
            return AddManualPlayerResult::InvalidInput;
        }

        $career = $this->careers->findById($careerId);
        if ($career === null) {
            return AddManualPlayerResult::CareerNotFound;
        }
        if (!$career->userId->equals($userId)) {
            return AddManualPlayerResult::NotOwner;
        }
        $season = $this->seasons->findActiveByCareerId($careerId);
        if ($season === null) {
            return AddManualPlayerResult::NoActiveSeason;
        }

        try {
            $this->transactions->run(function () use ($command, $userId, $birthDate, $position, $status, $season): void {
                $now = $this->clock->now();
                $player = Player::create($userId, $command->name, $birthDate, $command->nationalityCode, $now);
                $squadPlayer = SquadPlayer::add($player, $season->id, $position, $command->overall, $command->potential, $command->value, $status, $now);
                $this->players->add($player);
                $this->squadPlayers->add($squadPlayer);
            });
        } catch (DomainException) {
            return AddManualPlayerResult::InvalidInput;
        }

        return AddManualPlayerResult::Added;
    }
}
