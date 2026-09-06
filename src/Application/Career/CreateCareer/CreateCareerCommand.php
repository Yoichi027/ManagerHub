<?php

declare(strict_types=1);

namespace App\Application\Career\CreateCareer;

final readonly class CreateCareerCommand
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $managerName,
        public string $gameEdition,
        public string $clubId,
        public string $leagueId,
        public string $startsOn,
        public string $endsOn,
    ) {}
}
