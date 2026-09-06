<?php
declare(strict_types=1);
namespace App\Application\Squad\DeleteSquadPlayer;
enum DeleteSquadPlayerResult { case Deleted; case NotFound; case NotOwner; case InvalidInput; }
