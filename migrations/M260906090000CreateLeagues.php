<?php

declare(strict_types=1);

namespace App\Migrations;

use JsonException;
use RuntimeException;
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

        $seededAt = '2026-09-06 00:00:00.000000';
        $b->batchInsert(
            'leagues',
            [
                'id',
                'code',
                'name',
                'country',
                'logo_url',
                'season_start_month',
                'season_start_day',
                'season_end_month',
                'season_end_day',
                'created_at',
                'updated_at',
                'is_deleted',
                'deleted_at',
            ],
            array_map(
                static fn (array $league): array => [
                    'id' => $league['id'],
                    'code' => $league['code'],
                    'name' => $league['name'],
                    'country' => $league['country'],
                    'logo_url' => $league['logo_url'],
                    'season_start_month' => $league['season_start_month'],
                    'season_start_day' => $league['season_start_day'],
                    'season_end_month' => $league['season_end_month'],
                    'season_end_day' => $league['season_end_day'],
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
        $b->dropTable('leagues');
    }

    /** @return list<array<string, mixed>> */
    private static function catalogItems(): array
    {
        $path = dirname(__DIR__) . '/resources/catalog/fc26/v1/leagues.json';

        try {
            $catalog = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('The FC26 league catalog JSON is invalid.');
        }

        if (!is_array($catalog['items'] ?? null)) {
            throw new RuntimeException('The FC26 league catalog JSON does not contain items.');
        }

        return $catalog['items'];
    }
}
