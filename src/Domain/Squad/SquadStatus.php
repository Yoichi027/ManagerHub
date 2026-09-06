<?php

declare(strict_types=1);

namespace App\Domain\Squad;

enum SquadStatus: string
{
    case Active = 'Active';
    case LoanedOut = 'LoanedOut';
}
