<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use App\Domain\Catalog\Club;
use App\Domain\Catalog\ClubRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlClubRepository implements ClubRepository
{
    public function __construct(private ConnectionInterface $connection) {}

    public function findById(UuidInterface $id): ?Club
    {
        $row = $this->connection->select('*')->from('clubs')->where(['id' => $id->toString(), 'is_deleted' => false])->one();
        return $row === null ? null : $this->hydrate($row);
    }

    public function all(): array
    {
        return array_map($this->hydrate(...), $this->connection->select('*')->from('clubs')->where(['is_deleted' => false])->orderBy(['country' => SORT_ASC, 'name' => SORT_ASC])->all());
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Club
    {
        return new Club(Uuid::fromString((string) $row['id']), (string) $row['name'], (string) $row['country'], $row['logo_url'] === null ? null : (string) $row['logo_url'], $row['default_league_id'] === null ? null : Uuid::fromString((string) $row['default_league_id']));
    }
}
