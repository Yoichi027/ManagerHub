<?php

declare(strict_types=1);

namespace App\Infrastructure\Squad;

use App\Domain\Shared\CalendarDate;
use App\Domain\Squad\Position;
use App\Domain\Squad\SquadPlayer;
use App\Domain\Squad\SquadPlayerRepository;
use App\Domain\Squad\SquadStatus;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlSquadPlayerRepository implements SquadPlayerRepository
{
    public function __construct(private ConnectionInterface $connection) {}

    public function findBySeasonId(UuidInterface $seasonId): array
    {
        return array_map($this->hydrate(...), $this->connection->select('*')->from('squad_players')->where(['season_id' => $seasonId->toString(), 'is_deleted' => false])->orderBy(['player_name' => SORT_ASC])->all());
    }
    public function findById(UuidInterface $id): ?SquadPlayer
    {
        $row = $this->connection->select('*')->from('squad_players')->where(['id' => $id->toString(), 'is_deleted' => false])->one();
        return $row === null ? null : $this->hydrate($row);
    }

    public function add(SquadPlayer $player): void
    {
        $this->connection->createCommand()->insert('squad_players', $this->values($player))->execute();
    }
    public function save(SquadPlayer $player): void
    {
        $this->connection->createCommand()->update('squad_players', $this->values($player), ['id' => $player->id->toString()])->execute();
    }

    /** @return array<string, mixed> */
    private function values(SquadPlayer $player): array
    {
        return [
            'id' => $player->id->toString(), 'season_id' => $player->seasonId->toString(), 'player_id' => $player->playerId->toString(),
            'player_name' => $player->playerName, 'birth_date' => $player->birthDate->value, 'nationality_code' => $player->nationalityCode,
            'position' => $player->position->value, 'overall_initial' => $player->overallInitial, 'potential_initial' => $player->potentialInitial,
            'value_initial' => $player->valueInitial, 'overall_final' => $player->overallFinal, 'potential_final' => $player->potentialFinal,
            'value_final' => $player->valueFinal, 'status' => $player->status->value,
            'created_at' => $player->createdAt->format('Y-m-d H:i:s.u'), 'updated_at' => $player->updatedAt->format('Y-m-d H:i:s.u'),
            'is_deleted' => $player->isDeleted, 'deleted_at' => $player->deletedAt?->format('Y-m-d H:i:s.u'),
        ];
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): SquadPlayer
    {
        return new SquadPlayer(Uuid::fromString((string) $row['id']), Uuid::fromString((string) $row['season_id']), Uuid::fromString((string) $row['player_id']), (string) $row['player_name'], new CalendarDate((string) $row['birth_date']), (string) $row['nationality_code'], new Position((string) $row['position']), (int) $row['overall_initial'], (int) $row['potential_initial'], (string) $row['value_initial'], $row['overall_final'] === null ? null : (int) $row['overall_final'], $row['potential_final'] === null ? null : (int) $row['potential_final'], $row['value_final'] === null ? null : (string) $row['value_final'], SquadStatus::from((string) $row['status']), $this->instant((string) $row['created_at']), $this->instant((string) $row['updated_at']), (bool) $row['is_deleted'], $row['deleted_at'] === null ? null : $this->instant((string) $row['deleted_at']));
    }

    private function instant(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
