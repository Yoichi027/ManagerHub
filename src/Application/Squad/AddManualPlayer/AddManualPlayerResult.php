<?php

declare(strict_types=1);

namespace App\Application\Squad\AddManualPlayer;

enum AddManualPlayerResult
{
    case Added;
    case CareerNotFound;
    case NotOwner;
    case NoActiveSeason;
    case InvalidInput;
}
