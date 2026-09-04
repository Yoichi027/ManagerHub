<?php

declare(strict_types=1);

namespace App\Application\Identity\RegisterUser;

enum RegisterUserResult
{
    case Registered;
    case UsernameTaken;
    case EmailTaken;
    case InvalidInput;
}
