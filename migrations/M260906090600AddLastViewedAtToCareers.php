<?php
declare(strict_types=1);
namespace App\Migrations;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;
final class M260906090600AddLastViewedAtToCareers implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void { $b->addColumn('careers', 'last_viewed_at', $b->columnBuilder()::datetime(6)->null()); $b->createIndex('careers', 'ix_careers_user_last_viewed', ['user_id', 'last_viewed_at']); }
    public function down(MigrationBuilder $b): void { $b->dropIndex('careers', 'ix_careers_user_last_viewed'); $b->dropColumn('careers', 'last_viewed_at'); }
}
