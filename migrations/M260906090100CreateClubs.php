<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/** Creates global club metadata with an optional league suggestion. */
final class M260906090100CreateClubs implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /** @throws InvalidConfigException|NotSupportedException */
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('clubs', [
            'id' => $column::char(36)->primaryKey(),
            'code' => $column::string(64)->notNull()->unique(),
            'name' => $column::string(120)->notNull(),
            'country' => $column::string(100)->notNull(),
            'logo_url' => $column::string(255)->null(),
            'default_league_id' => $column::char(36)->null(),
            'created_at' => $column::datetime(6)->notNull(),
            'updated_at' => $column::datetime(6)->notNull(),
            'is_deleted' => $column::boolean()->notNull(),
            'deleted_at' => $column::datetime(6)->null(),
        ]);
        $b->createIndex('ux_clubs_name_country', 'clubs', ['name', 'country'], 'UNIQUE');
        $b->addForeignKey(
            'clubs',
            'fk_clubs_default_league',
            'default_league_id',
            'leagues',
            'id',
            ReferentialAction::SET_NULL,
        );
    }

    /** @throws InvalidConfigException|NotSupportedException */
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('clubs', 'fk_clubs_default_league');
        $b->dropTable('clubs');
    }
}
