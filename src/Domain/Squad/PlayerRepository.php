<?php

declare(strict_types=1);

namespace App\Domain\Squad;

interface PlayerRepository
{
    public function add(Player $player): void;
}
