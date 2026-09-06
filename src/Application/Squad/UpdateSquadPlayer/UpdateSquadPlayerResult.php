<?php
declare(strict_types=1);
namespace App\Application\Squad\UpdateSquadPlayer;
enum UpdateSquadPlayerResult { case Updated; case NotFound; case NotOwner; case InvalidInput; }
