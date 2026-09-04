<?php

declare(strict_types=1);

use Yiisoft\Db\Mysql\Dsn;

return [
    'yiisoft/db-mysql' => [
        'dsn' => new Dsn('mysql', '127.0.0.1', 'manager_hub_test', '3306', ['charset' => 'utf8mb4']),
    ],
];
