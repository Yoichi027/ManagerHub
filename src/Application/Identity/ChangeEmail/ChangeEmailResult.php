<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangeEmail;

enum ChangeEmailResult
{
    case Changed;
    case EmailTaken;
    case InvalidInput;
    case UserNotFound;
}
