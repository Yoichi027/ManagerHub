<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangePassword;

enum ChangePasswordResult
{
    case Changed;
    case CurrentPasswordIncorrect;
    case InvalidPassword;
    case UserNotFound;
}
