<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/** Creates career seasons with explicit club and league choices plus historical snapshots. */
final class M260906090300CreateSeasons implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /** @throws InvalidConfigException|NotSupportedException */
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('seasons', [
            'id' => $column::char(36)->primaryKey(),
            'career_id' => $column::char(36)->notNull(),
            'managed_club_id' => $column::char(36)->null(),
            'managed_club_name' => $column::string(120)->notNull(),
            'league_id' => $column::char(36)->null(),
            'league_name' => $column::string(100)->notNull(),
            'label' => $column::string(20)->notNull(),
            'starts_on' => $column::date()->notNull(),
            'ends_on' => $column::date()->notNull(),
            'finalized_at' => $column::datetime(6)->null(),
            'created_at' => $column::datetime(6)->notNull(),
            'updated_at' => $column::datetime(6)->notNull(),
            'is_deleted' => $column::boolean()->notNull(),
            'deleted_at' => $column::datetime(6)->null(),
        ]);
        $b->createIndex('ix_seasons_career_finalized', 'seasons', ['career_id', 'finalized_at']);
        $b->createIndex('ux_seasons_career_label', 'seasons', ['career_id', 'label'], 'UNIQUE');
        $b->addForeignKey('seasons', 'fk_seasons_career', 'career_id', 'careers', 'id', ReferentialAction::RESTRICT);
        $b->addForeignKey('seasons', 'fk_seasons_managed_club', 'managed_club_id', 'clubs', 'id', ReferentialAction::SET_NULL);
        $b->addForeignKey('seasons', 'fk_seasons_league', 'league_id', 'leagues', 'id', ReferentialAction::SET_NULL);
    }

    /** @throws InvalidConfigException|NotSupportedException */
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('seasons', 'fk_seasons_league');
        $b->dropForeignKey('seasons', 'fk_seasons_managed_club');
        $b->dropForeignKey('seasons', 'fk_seasons_career');
        $b->dropTable('seasons');
    }
}
