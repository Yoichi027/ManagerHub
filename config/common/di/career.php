<?php

declare(strict_types=1);

use App\Application\Shared\Transaction\TransactionManager;
use App\Domain\Career\CareerRepository;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Catalog\LeagueRepository;
use App\Domain\Season\SeasonRepository;
use App\Infrastructure\Career\MysqlCareerRepository;
use App\Infrastructure\Catalog\MysqlClubRepository;
use App\Infrastructure\Catalog\MysqlLeagueRepository;
use App\Infrastructure\Season\MysqlSeasonRepository;
use App\Infrastructure\Shared\Transaction\MysqlTransactionManager;

return [
    CareerRepository::class => MysqlCareerRepository::class,
    SeasonRepository::class => MysqlSeasonRepository::class,
    ClubRepository::class => MysqlClubRepository::class,
    LeagueRepository::class => MysqlLeagueRepository::class,
    TransactionManager::class => MysqlTransactionManager::class,
];
