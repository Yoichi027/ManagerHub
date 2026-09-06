<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M260906090400CreatePlayers implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $c = $b->columnBuilder();
        $b->createTable('players', ['id' => $c::char(36)->primaryKey(),'owner_user_id' => $c::char(36)->null(),'name' => $c::string(120)->notNull(),'birth_date' => $c::date()->notNull(),'nationality_code' => $c::char(2)->notNull(),'created_at' => $c::datetime(6)->notNull(),'updated_at' => $c::datetime(6)->notNull(),'is_deleted' => $c::boolean()->notNull(),'deleted_at' => $c::datetime(6)->null()]);
        $b->createIndex('players', 'ix_players_owner_name', ['owner_user_id','name']);
        $b->addForeignKey('players', 'fk_players_owner', 'owner_user_id', 'users', 'id', ReferentialAction::SET_NULL);
    }
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('players', 'fk_players_owner');
        $b->dropTable('players');
    }
}
