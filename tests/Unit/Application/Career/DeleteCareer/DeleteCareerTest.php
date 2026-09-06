<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Career\DeleteCareer;

use App\Application\Career\DeleteCareer\DeleteCareer;
use App\Application\Career\DeleteCareer\DeleteCareerCommand;
use App\Application\Career\DeleteCareer\DeleteCareerResult;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Career\Career;
use App\Domain\Career\CareerName;
use App\Domain\Career\CareerRepository;
use App\Domain\Career\GameEdition;
use App\Domain\Career\ManagerName;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

final class DeleteCareerTest extends \Codeception\Test\Unit
{
    public function testDeletesOnlyTheOwnersCareer(): void
    {
        $owner = Uuid::uuid7();
        $otherUser = Uuid::uuid7();
        $career = Career::start(new CareerName('Career'), new ManagerName('Manager'), new GameEdition('FC26'), $owner, $this->now());
        $repository = new DeleteCareerRepository($career);
        $service = new DeleteCareer($repository, new DeleteCareerClock());

        self::assertSame(DeleteCareerResult::NotOwner, $service->delete(new DeleteCareerCommand($career->id->toString(), $otherUser->toString())));
        self::assertFalse($career->isDeleted);
        self::assertSame(DeleteCareerResult::Deleted, $service->delete(new DeleteCareerCommand($career->id->toString(), $owner->toString())));
        self::assertTrue($career->isDeleted);
        self::assertSame(1, $repository->saveCount);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-06 12:00:00', new DateTimeZone('UTC'));
    }
}

final class DeleteCareerClock implements UtcClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-06 12:01:00', new DateTimeZone('UTC'));
    }
}
final class DeleteCareerRepository implements CareerRepository
{
    public int $saveCount = 0;
    public function __construct(private Career $career) {} public function findById(\Ramsey\Uuid\UuidInterface $id): ?Career
    {
        return $id->equals($this->career->id) ? $this->career : null;
    } public function findByUserId(\Ramsey\Uuid\UuidInterface $id): array
    {
        return [];
    } public function add(Career $career): void {} public function save(Career $career): void
    {
        $this->saveCount++;
    }
}
