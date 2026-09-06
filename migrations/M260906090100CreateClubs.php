<?php

declare(strict_types=1);

namespace App\Migrations;

use JsonException;
use RuntimeException;
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

        $seededAt = '2026-09-06 00:00:00.000000';
        $b->batchInsert(
            'clubs',
            [
                'id',
                'code',
                'name',
                'country',
                'logo_url',
                'default_league_id',
                'created_at',
                'updated_at',
                'is_deleted',
                'deleted_at',
            ],
            array_map(
                static fn (array $club): array => [
                    'id' => $club['id'],
                    'code' => $club['code'],
                    'name' => $club['name'],
                    'country' => $club['country'],
                    'logo_url' => $club['logo_url'],
                    'default_league_id' => $club['default_league_id'],
                    'created_at' => $seededAt,
                    'updated_at' => $seededAt,
                    'is_deleted' => false,
                    'deleted_at' => null,
                ],
                self::catalogItems(),
            ),
        );
    }

    /** @throws InvalidConfigException|NotSupportedException */
    public function down(MigrationBuilder $b): void
    {
        $b->dropForeignKey('clubs', 'fk_clubs_default_league');
        $b->dropTable('clubs');
    }

    /** @return list<array<string, mixed>> */
    private static function catalogItems(): array
    {
        $path = dirname(__DIR__) . '/resources/catalog/fc26/v1/clubs.json';

        try {
            $catalog = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('The FC26 club catalog JSON is invalid.');
        }

        if (!is_array($catalog['items'] ?? null)) {
            throw new RuntimeException('The FC26 club catalog JSON does not contain items.');
        }

        return $catalog['items'];
    }
}
