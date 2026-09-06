<?php

declare(strict_types=1);

namespace App\Application\Shared\Transaction;

use Closure;

interface TransactionManager
{
    public function run(Closure $operation): mixed;
}
