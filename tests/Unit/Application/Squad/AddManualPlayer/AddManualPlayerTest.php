<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Squad\AddManualPlayer;

use App\Application\Shared\Time\UtcClock;
use App\Application\Shared\Transaction\TransactionManager;
use App\Application\Squad\AddManualPlayer\AddManualPlayer;
use App\Application\Squad\AddManualPlayer\AddManualPlayerCommand;
use App\Application\Squad\AddManualPlayer\AddManualPlayerResult;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayer;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayerCommand;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayerResult;
use App\Domain\Career\Career;
use App\Domain\Career\CareerName;
use App\Domain\Career\CareerRepository;
use App\Domain\Career\GameEdition;
use App\Domain\Career\ManagerName;
use App\Domain\Season\ManagedClub;
use App\Domain\Season\ManagedLeague;
use App\Domain\Season\Season;
use App\Domain\Season\SeasonLabel;
use App\Domain\Season\SeasonRepository;
use App\Domain\Shared\CalendarDate;
use App\Domain\Squad\Player;
use App\Domain\Squad\PlayerRepository;
use App\Domain\Squad\SquadPlayer;
use App\Domain\Squad\SquadPlayerRepository;
use App\Domain\Squad\Position;
use App\Domain\Squad\SquadStatus;
use Closure;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

final class AddManualPlayerTest extends Unit
{
    public function testAddsUserOwnedPlayerToTheActiveSeasonInOneTransaction(): void
    {
        $now = new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC'));
        $owner = Uuid::uuid7($now);
        $career = Career::start(new CareerName('Sporting'), new ManagerName('Rui Costa'), new GameEdition('FC26'), $owner, $now);
        $season = Season::open($career->id, new ManagedClub(null, 'Sporting CP'), new ManagedLeague(null, 'Liga Portugal'), new SeasonLabel('2026/27'), new CalendarDate('2026-07-01'), new CalendarDate('2027-06-30'), $now);
        $players = new StoredPlayers(); $squad = new StoredSquadPlayers(); $transactions = new ImmediateTransaction();
        $service = new AddManualPlayer(new OneCareer($career), new OneSeason($season), $players, $squad, $transactions, new Clock($now));

        $result = $service->add(new AddManualPlayerCommand($career->id->toString(), $owner->toString(), 'Pedro Gonçalves', '1998-06-28', 'PT', 'CAM', 80, 82, '28500000.00'));

        self::assertSame(AddManualPlayerResult::Added, $result);
        self::assertTrue($transactions->ran);
        self::assertCount(1, $players->items);
        self::assertCount(1, $squad->items);
        self::assertSame($season->id->toString(), $squad->items[0]->seasonId->toString());
        self::assertSame('1998-06-28', $squad->items[0]->birthDate->value);
        self::assertSame('PT', $squad->items[0]->nationalityCode);
    }

    public function testDoesNotAddAPlayerToAnotherUsersCareer(): void
    {
        $now = new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC')); $owner = Uuid::uuid7($now);
        $career = Career::start(new CareerName('Sporting'), new ManagerName('Rui Costa'), new GameEdition('FC26'), $owner, $now);
        $players = new StoredPlayers(); $squad = new StoredSquadPlayers();
        $service = new AddManualPlayer(new OneCareer($career), new OneSeason(null), $players, $squad, new ImmediateTransaction(), new Clock($now));

        $result = $service->add(new AddManualPlayerCommand($career->id->toString(), Uuid::uuid7($now)->toString(), 'Player', '2000-01-01', 'PT', 'CM', 70, 75, '0'));

        self::assertSame(AddManualPlayerResult::NotOwner, $result);
        self::assertSame([], $players->items);
        self::assertSame([], $squad->items);
    }

    public function testSoftDeletesAPlayerFromTheOwnersSquad(): void
    {
        $now = new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC')); $owner = Uuid::uuid7($now);
        $career = Career::start(new CareerName('Sporting'), new ManagerName('Rui Costa'), new GameEdition('FC26'), $owner, $now);
        $season = Season::open($career->id, new ManagedClub(null, 'Sporting CP'), new ManagedLeague(null, 'Liga Portugal'), new SeasonLabel('2026/27'), new CalendarDate('2026-07-01'), new CalendarDate('2027-06-30'), $now);
        $player = Player::create($owner, 'Player', new CalendarDate('2000-01-01'), 'PT', $now); $squad = new StoredSquadPlayers();
        $squadPlayer = SquadPlayer::add($player, $season->id, new Position('CM'), 70, 75, '1000000.00', SquadStatus::Starter, $now); $squad->add($squadPlayer);
        $service = new DeleteSquadPlayer(new OneCareer($career), new OneSeason($season), $squad, new Clock($now->modify('+1 second')));

        $result = $service->delete(new DeleteSquadPlayerCommand($career->id->toString(), $owner->toString(), $squadPlayer->id->toString()));

        self::assertSame(DeleteSquadPlayerResult::Deleted, $result); self::assertTrue($squadPlayer->isDeleted); self::assertNotNull($squadPlayer->deletedAt);
    }
}

final readonly class Clock implements UtcClock { public function __construct(private DateTimeImmutable $now) {} public function now(): DateTimeImmutable { return $this->now; } }
final class ImmediateTransaction implements TransactionManager { public bool $ran = false; public function run(Closure $operation): mixed { $this->ran = true; return $operation(); } }
final readonly class OneCareer implements CareerRepository { public function __construct(private Career $career) {} public function findById(\Ramsey\Uuid\UuidInterface $id): ?Career { return $id->equals($this->career->id) ? $this->career : null; } public function findByUserId(\Ramsey\Uuid\UuidInterface $id): array { return []; } public function add(Career $career): void {} public function save(Career $career): void {} }
final readonly class OneSeason implements SeasonRepository { public function __construct(private ?Season $season) {} public function findById(\Ramsey\Uuid\UuidInterface $id): ?Season { return $this->season !== null && $id->equals($this->season->id) ? $this->season : null; } public function findActiveByCareerId(\Ramsey\Uuid\UuidInterface $id): ?Season { return $this->season; } public function add(Season $season): void {} public function save(Season $season): void {} }
final class StoredPlayers implements PlayerRepository { /** @var list<Player> */ public array $items = []; public function add(Player $player): void { $this->items[] = $player; } }
final class StoredSquadPlayers implements SquadPlayerRepository { /** @var list<SquadPlayer> */ public array $items = []; public function findBySeasonId(\Ramsey\Uuid\UuidInterface $seasonId): array { return []; } public function findById(\Ramsey\Uuid\UuidInterface $id): ?SquadPlayer { foreach ($this->items as $item) if ($item->id->equals($id)) return $item; return null; } public function add(SquadPlayer $player): void { $this->items[] = $player; } public function save(SquadPlayer $player): void {} }
