<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use App\Domain\Catalog\League;
use App\Domain\Catalog\LeagueRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlLeagueRepository implements LeagueRepository
{
    public function __construct(private ConnectionInterface $connection) {}

    public function findById(UuidInterface $id): ?League
    {
        $row = $this->connection->select('*')->from('leagues')->where(['id' => $id->toString(), 'is_deleted' => false])->one();
        return $row === null ? null : $this->hydrate($row);
    }

    public function all(): array
    {
        return array_map($this->hydrate(...), $this->connection->select('*')->from('leagues')->where(['is_deleted' => false])->orderBy(['country' => SORT_ASC, 'name' => SORT_ASC])->all());
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): League
    {
        return new League(Uuid::fromString((string) $row['id']), (string) $row['name'], (string) $row['country'], $row['logo_url'] === null ? null : (string) $row['logo_url'], (int) $row['season_start_month'], (int) $row['season_start_day'], (int) $row['season_end_month'], (int) $row['season_end_day']);
    }
}
