<?php

declare(strict_types=1);

namespace App\Infrastructure\Season;

use App\Domain\Season\ManagedClub;
use App\Domain\Season\ManagedLeague;
use App\Domain\Season\Season;
use App\Domain\Season\SeasonLabel;
use App\Domain\Season\SeasonRepository;
use App\Domain\Shared\CalendarDate;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlSeasonRepository implements SeasonRepository
{
    public function __construct(private ConnectionInterface $connection) {}
    public function findById(UuidInterface $id): ?Season
    {
        $row = $this->connection->select('*')->from('seasons')->where(['id' => $id->toString(), 'is_deleted' => false])->one();
        return $row === null ? null : $this->hydrate($row);
    }
    public function findActiveByCareerId(UuidInterface $careerId): ?Season
    {
        $row = $this->connection->select('*')->from('seasons')->where(['career_id' => $careerId->toString(), 'is_deleted' => false, 'finalized_at' => null])->one();
        return $row === null ? null : $this->hydrate($row);
    }
    public function add(Season $season): void
    {
        $this->connection->createCommand()->insert('seasons', $this->values($season))->execute();
    }
    public function save(Season $season): void
    {
        $this->connection->createCommand()->update('seasons', $this->values($season), ['id' => $season->id->toString()])->execute();
    }
    /** @return array<string, mixed> */ private function values(Season $s): array
    {
        return ['id' => $s->id->toString(),'career_id' => $s->careerId->toString(),'managed_club_id' => $s->managedClub->id?->toString(),'managed_club_name' => $s->managedClub->name,'league_id' => $s->managedLeague->id?->toString(),'league_name' => $s->managedLeague->name,'label' => $s->label->value,'starts_on' => $s->startsOn->value,'ends_on' => $s->endsOn->value,'finalized_at' => $s->finalizedAt === null ? null : $this->dateTime($s->finalizedAt),'created_at' => $this->dateTime($s->createdAt),'updated_at' => $this->dateTime($s->updatedAt),'is_deleted' => $s->isDeleted,'deleted_at' => $s->deletedAt === null ? null : $this->dateTime($s->deletedAt)];
    }
    /** @param array<string, mixed> $r */ private function hydrate(array $r): Season
    {
        return Season::reconstitute(Uuid::fromString((string) $r['id']), Uuid::fromString((string) $r['career_id']), new ManagedClub($r['managed_club_id'] === null ? null : Uuid::fromString((string) $r['managed_club_id']), (string) $r['managed_club_name']), new ManagedLeague($r['league_id'] === null ? null : Uuid::fromString((string) $r['league_id']), (string) $r['league_name']), new SeasonLabel((string) $r['label']), new CalendarDate((string) $r['starts_on']), new CalendarDate((string) $r['ends_on']), $r['finalized_at'] === null ? null : $this->instant((string) $r['finalized_at']), $this->instant((string) $r['created_at']), $this->instant((string) $r['updated_at']), (bool) $r['is_deleted'], $r['deleted_at'] === null ? null : $this->instant((string) $r['deleted_at']));
    }
    private function dateTime(DateTimeImmutable $value): string
    {
        return $value->format('Y-m-d H:i:s.u');
    }
    private function instant(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
