<?php

declare(strict_types=1);

namespace App\Application\Career\CreateCareer;

use App\Application\Shared\Time\UtcClock;
use App\Application\Shared\Transaction\TransactionManager;
use App\Domain\Career\Career;
use App\Domain\Career\CareerName;
use App\Domain\Career\CareerRepository;
use App\Domain\Career\GameEdition;
use App\Domain\Career\ManagerName;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Catalog\LeagueRepository;
use App\Domain\Season\ManagedClub;
use App\Domain\Season\ManagedLeague;
use App\Domain\Season\Season;
use App\Domain\Season\SeasonLabel;
use App\Domain\Season\SeasonRepository;
use App\Domain\Shared\CalendarDate;
use DomainException;
use Ramsey\Uuid\Uuid;

final readonly class CreateCareer
{
    public function __construct(
        private CareerRepository $careers,
        private SeasonRepository $seasons,
        private ClubRepository $clubs,
        private LeagueRepository $leagues,
        private TransactionManager $transactions,
        private UtcClock $clock,
    ) {}

    public function create(CreateCareerCommand $command): CreateCareerResult
    {
        try {
            $userId = Uuid::fromString($command->userId);
            $clubId = Uuid::fromString($command->clubId);
            $leagueId = Uuid::fromString($command->leagueId);
            $name = new CareerName($command->name);
            $managerName = new ManagerName($command->managerName);
            $edition = new GameEdition($command->gameEdition);
            $startsOn = new CalendarDate($command->startsOn);
            $endsOn = new CalendarDate($command->endsOn);
            $label = SeasonLabel::fromPeriod($startsOn, $endsOn);
        } catch (\Throwable) {
            return CreateCareerResult::InvalidInput;
        }

        $club = $this->clubs->findById($clubId);
        if ($club === null) {
            return CreateCareerResult::ClubNotFound;
        }
        $league = $this->leagues->findById($leagueId);
        if ($league === null) {
            return CreateCareerResult::LeagueNotFound;
        }

        try {
            $this->transactions->run(function () use ($name, $managerName, $edition, $userId, $club, $league, $label, $startsOn, $endsOn): void {
                $now = $this->clock->now();
                $career = Career::start($name, $managerName, $edition, $userId, $now);
                $season = Season::open(
                    $career->id,
                    new ManagedClub($club->id, $club->name),
                    new ManagedLeague($league->id, $league->name),
                    $label,
                    $startsOn,
                    $endsOn,
                    $now,
                );
                $this->careers->add($career);
                $this->seasons->add($season);
            });
        } catch (DomainException) {
            return CreateCareerResult::InvalidInput;
        }

        return CreateCareerResult::Created;
    }
}
