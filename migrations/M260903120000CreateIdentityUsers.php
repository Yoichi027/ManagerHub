<?php

declare(strict_types=1);

namespace App\Migrations;

use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/**
 * Creates the table that stores application users and their login credentials.
 */
final class M260903120000CreateIdentityUsers implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('users', [
            'id' => $column::primaryKey(),
            'username' => $column::string(100)->notNull()->unique(),
            'email' => $column::string(255)->notNull()->unique(),
            'password_hash' => $column::string(255)->notNull(),
            'created_at' => $column::datetime()->notNull(),
            'updated_at' => $column::datetime()->notNull(),
            'is_deleted' => $column::boolean()->notNull(),
            'deleted_at' => $column::datetime()->null(),
        ]);
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('users');
    }
}
