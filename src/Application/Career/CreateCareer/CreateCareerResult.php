<?php

declare(strict_types=1);

namespace App\Application\Career\CreateCareer;

enum CreateCareerResult
{
    case Created;
    case InvalidInput;
    case ClubNotFound;
    case LeagueNotFound;
}
