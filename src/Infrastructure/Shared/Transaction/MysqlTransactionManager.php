<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Transaction;

use App\Application\Shared\Transaction\TransactionManager;
use Closure;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlTransactionManager implements TransactionManager
{
    public function __construct(private ConnectionInterface $connection) {}

    public function run(Closure $operation): mixed
    {
        return $this->connection->transaction($operation);
    }
}
