<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Career\CreateCareer;

use App\Application\Career\CreateCareer\CreateCareer;
use App\Application\Career\CreateCareer\CreateCareerCommand;
use App\Application\Career\CreateCareer\CreateCareerResult;
use App\Application\Shared\Time\UtcClock;
use App\Application\Shared\Transaction\TransactionManager;
use App\Domain\Career\Career;
use App\Domain\Career\CareerRepository;
use App\Domain\Catalog\Club;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Catalog\League;
use App\Domain\Catalog\LeagueRepository;
use App\Domain\Season\Season;
use App\Domain\Season\SeasonRepository;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

final class CreateCareerTest extends \Codeception\Test\Unit
{
    public function testCreatesCareerAndFirstEmptySeasonInOneTransaction(): void
    {
        $club = new Club(Uuid::uuid7(), 'Sporting CP', 'Portugal', null, null);
        $league = new League(Uuid::uuid7(), 'Liga Portugal', 'Portugal', null, 7, 1, 6, 30);
        $careers = new InMemoryCareers(); $seasons = new InMemorySeasons(); $transactions = new ImmediateTransactionManager();
        $service = new CreateCareer($careers, $seasons, new SingleClubRepository($club), new SingleLeagueRepository($league), $transactions, new FixedClock());

        $result = $service->create(new CreateCareerCommand(Uuid::uuid7()->toString(), 'My Sporting career', 'Rui Costa', 'FC26', $club->id->toString(), $league->id->toString(), '2026-07-01', '2027-06-30'));

        self::assertSame(CreateCareerResult::Created, $result);
        self::assertTrue($transactions->ran);
        self::assertCount(1, $careers->items);
        self::assertSame('Rui Costa', $careers->items[0]->managerName->value);
        self::assertCount(1, $seasons->items);
        self::assertSame('Sporting CP', $seasons->items[0]->managedClub->name);
        self::assertSame('Liga Portugal', $seasons->items[0]->managedLeague->name);
        self::assertSame('2026/27', $seasons->items[0]->label->value);
        self::assertNull($seasons->items[0]->finalizedAt);
    }
}

final class FixedClock implements UtcClock { public function now(): DateTimeImmutable { return new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC')); } }
final class ImmediateTransactionManager implements TransactionManager { public bool $ran = false; public function run(Closure $operation): mixed { $this->ran = true; return $operation(); } }
final class InMemoryCareers implements CareerRepository { /** @var list<Career> */ public array $items = []; public function findById(\Ramsey\Uuid\UuidInterface $id): ?Career { return null; } public function findByUserId(\Ramsey\Uuid\UuidInterface $id): array { return []; } public function add(Career $career): void { $this->items[] = $career; } public function save(Career $career): void {} }
final class InMemorySeasons implements SeasonRepository { /** @var list<Season> */ public array $items = []; public function findById(\Ramsey\Uuid\UuidInterface $id): ?Season { return null; } public function findActiveByCareerId(\Ramsey\Uuid\UuidInterface $id): ?Season { return null; } public function add(Season $season): void { $this->items[] = $season; } public function save(Season $season): void {} }
final readonly class SingleClubRepository implements ClubRepository { public function __construct(private Club $club) {} public function findById(\Ramsey\Uuid\UuidInterface $id): ?Club { return $id->equals($this->club->id) ? $this->club : null; } public function all(): array { return [$this->club]; } }
final readonly class SingleLeagueRepository implements LeagueRepository { public function __construct(private League $league) {} public function findById(\Ramsey\Uuid\UuidInterface $id): ?League { return $id->equals($this->league->id) ? $this->league : null; } public function all(): array { return [$this->league]; } }
