<?php

declare(strict_types=1);

namespace App\Infrastructure\Career;

use App\Domain\Career\Career;
use App\Domain\Career\CareerName;
use App\Domain\Career\CareerRepository;
use App\Domain\Career\GameEdition;
use App\Domain\Career\ManagerName;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlCareerRepository implements CareerRepository
{
    public function __construct(private ConnectionInterface $connection) {}
    public function findById(UuidInterface $id): ?Career { $row = $this->connection->select('*')->from('careers')->where(['id' => $id->toString(), 'is_deleted' => false])->one(); return $row === null ? null : $this->hydrate($row); }
    public function findByUserId(UuidInterface $userId): array { return array_map($this->hydrate(...), $this->connection->select('*')->from('careers')->where(['user_id' => $userId->toString(), 'is_deleted' => false])->orderBy(['updated_at' => SORT_DESC])->all()); }
    public function add(Career $career): void { $this->connection->createCommand()->insert('careers', $this->values($career))->execute(); }
    public function save(Career $career): void { $this->connection->createCommand()->update('careers', $this->values($career), ['id' => $career->id->toString()])->execute(); }
    /** @return array<string, mixed> */ private function values(Career $career): array { return ['id' => $career->id->toString(), 'user_id' => $career->userId->toString(), 'name' => $career->name->value, 'manager_name' => $career->managerName->value, 'game_edition' => $career->gameEdition->value, 'created_at' => $this->dateTime($career->createdAt), 'updated_at' => $this->dateTime($career->updatedAt), 'is_deleted' => $career->isDeleted, 'deleted_at' => $career->deletedAt === null ? null : $this->dateTime($career->deletedAt)]; }
    /** @param array<string, mixed> $r */ private function hydrate(array $r): Career { return Career::reconstitute(Uuid::fromString((string) $r['id']), Uuid::fromString((string) $r['user_id']), new CareerName((string) $r['name']), new ManagerName((string) $r['manager_name']), new GameEdition((string) $r['game_edition']), $this->instant((string) $r['created_at']), $this->instant((string) $r['updated_at']), (bool) $r['is_deleted'], $r['deleted_at'] === null ? null : $this->instant((string) $r['deleted_at'])); }
    private function dateTime(DateTimeImmutable $value): string { return $value->format('Y-m-d H:i:s.u'); }
    private function instant(string $value): DateTimeImmutable { return new DateTimeImmutable($value, new DateTimeZone('UTC')); }
}
