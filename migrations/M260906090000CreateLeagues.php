<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/** Creates the global league metadata used to prefill season dates. */
final class M260906090000CreateLeagues implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /** @throws InvalidConfigException|NotSupportedException */
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('leagues', [
            'id' => $column::char(36)->primaryKey(),
            'code' => $column::string(64)->notNull()->unique(),
            'name' => $column::string(100)->notNull(),
            'country' => $column::string(100)->notNull(),
            'logo_url' => $column::string(255)->null(),
            'season_start_month' => $column::integer()->notNull(),
            'season_start_day' => $column::integer()->notNull(),
            'season_end_month' => $column::integer()->notNull(),
            'season_end_day' => $column::integer()->notNull(),
            'created_at' => $column::datetime(6)->notNull(),
            'updated_at' => $column::datetime(6)->notNull(),
            'is_deleted' => $column::boolean()->notNull(),
            'deleted_at' => $column::datetime(6)->null(),
        ]);
    }

    /** @throws InvalidConfigException|NotSupportedException */
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('leagues');
    }
}
