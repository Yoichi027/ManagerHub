<?php

declare(strict_types=1);

namespace App\Application\Identity\DeactivateAccount;

enum DeactivateAccountResult
{
    case Deactivated;
    case PasswordIncorrect;
    case UserNotFound;
}
