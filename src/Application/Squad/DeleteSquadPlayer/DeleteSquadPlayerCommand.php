<?php
declare(strict_types=1);
namespace App\Application\Squad\DeleteSquadPlayer;
final readonly class DeleteSquadPlayerCommand { public function __construct(public string $careerId, public string $userId, public string $squadPlayerId) {} }
