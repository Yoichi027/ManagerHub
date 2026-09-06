<?php

declare(strict_types=1);

namespace App\Domain\Squad;

enum SquadStatus: string
{
    case Starter = 'Starter';
    case Substitute = 'Substitute';
    case Active = 'Active';
    case LoanedOut = 'LoanedOut';
}
