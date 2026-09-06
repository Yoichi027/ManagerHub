<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/** Creates user-owned career saves without embedding season-specific club state. */
final class M260906090200CreateCareers implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /** @throws InvalidConfigException|NotSupportedException */
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('careers', [
            'id' => $column::char(36)->primaryKey(),
            'user_id' => $column::char(36)->notNull(),
            'name' => $column::string(100)->notNull(),
            'game_edition' => $column::string(32)->notNull(),
            'created_at' => $column::datetime(6)->notNull(),
            'updated_at' => $column::datetime(6)->notNull(),
            'is_deleted' => $column::boolean()->notNull(),
            'deleted_at' => $column::datetime(6)->null(),
        ]);
        $b->createIndex('ix_careers_user_id', 'careers', 'user_id');
        $b->addForeignKey('careers', 'fk_careers_user', 'user_id', 'users', 'id', ReferentialAction::RESTRICT);
    }

    /** @throws InvalidConfigException|NotSupportedException */
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('careers', 'fk_careers_user');
        $b->dropTable('careers');
    }
}
