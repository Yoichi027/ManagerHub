<?php

declare(strict_types=1);

namespace App\Application\Career\DeleteCareer;

final readonly class DeleteCareerCommand
{
    public function __construct(public string $careerId, public string $userId) {}
}
