<?php

declare(strict_types=1);

namespace App\Infrastructure\Squad;

use App\Domain\Squad\Player;
use App\Domain\Squad\PlayerRepository;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlPlayerRepository implements PlayerRepository
{
    public function __construct(private ConnectionInterface $connection) {}

    public function add(Player $player): void
    {
        $this->connection->createCommand()->insert('players', [
            'id' => $player->id->toString(),
            'owner_user_id' => $player->ownerUserId?->toString(),
            'name' => $player->name,
            'birth_date' => $player->birthDate->value,
            'nationality_code' => $player->nationalityCode,
            'created_at' => $player->createdAt->format('Y-m-d H:i:s.u'),
            'updated_at' => $player->updatedAt->format('Y-m-d H:i:s.u'),
            'is_deleted' => $player->isDeleted,
            'deleted_at' => $player->deletedAt?->format('Y-m-d H:i:s.u'),
        ])->execute();
    }
}
