<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M260906090500CreateSquadPlayers implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $c = $b->columnBuilder();
        $b->createTable('squad_players', ['id' => $c::char(36)->primaryKey(),'season_id' => $c::char(36)->notNull(),'player_id' => $c::char(36)->notNull(),'player_name' => $c::string(120)->notNull(),'birth_date' => $c::date()->notNull(),'nationality_code' => $c::char(2)->notNull(),'position' => $c::string(3)->notNull(),'overall_initial' => $c::integer()->notNull(),'potential_initial' => $c::integer()->notNull(),'value_initial' => $c::decimal(15, 2)->notNull(),'overall_final' => $c::integer()->null(),'potential_final' => $c::integer()->null(),'value_final' => $c::decimal(15, 2)->null(),'status' => $c::string(24)->notNull(),'created_at' => $c::datetime(6)->notNull(),'updated_at' => $c::datetime(6)->notNull(),'is_deleted' => $c::boolean()->notNull(),'deleted_at' => $c::datetime(6)->null()]);
        $b->createIndex('squad_players', 'ux_squad_players_season_player', ['season_id','player_id'], 'UNIQUE');
        $b->addForeignKey('squad_players', 'fk_squad_players_season', 'season_id', 'seasons', 'id', ReferentialAction::RESTRICT);
        $b->addForeignKey('squad_players', 'fk_squad_players_player', 'player_id', 'players', 'id', ReferentialAction::RESTRICT);
    }
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('squad_players', 'fk_squad_players_player');
        $b->dropForeignKey('squad_players', 'fk_squad_players_season');
        $b->dropTable('squad_players');
    }
}
