<?php

declare(strict_types=1);

use App\Application\Identity\PasswordHasher;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Identity\UserRepository;
use App\Infrastructure\Identity\MysqlUserRepository;
use App\Infrastructure\Identity\YiiPasswordHasher;
use App\Infrastructure\Shared\Time\SystemUtcClock;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Security\PasswordHasher as YiiPasswordHasherService;

return [
    YiiPasswordHasherService::class => [
        '__construct()' => [
            'algorithm' => PASSWORD_ARGON2ID,
        ],
    ],
    PasswordHasher::class => YiiPasswordHasher::class,
    UtcClock::class => SystemUtcClock::class,
    UserRepository::class => MysqlUserRepository::class,
    IdentityRepositoryInterface::class => MysqlUserRepository::class,
];
