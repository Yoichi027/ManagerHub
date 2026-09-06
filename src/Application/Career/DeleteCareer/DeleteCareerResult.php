<?php

declare(strict_types=1);

namespace App\Application\Career\DeleteCareer;

enum DeleteCareerResult
{
    case Deleted;
    case NotFound;
    case NotOwner;
    case InvalidInput;
}
