<?php
declare(strict_types=1);
namespace App\Web\Career\Squad;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayer;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayerCommand;
use App\Application\Squad\DeleteSquadPlayer\DeleteSquadPlayerResult;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\User\CurrentUser;
final readonly class DeleteSquadPlayerAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private CurrentRoute $route, private CurrentUser $currentUser, private DeleteSquadPlayer $deletePlayer, private ResponseFactoryInterface $responses) {}
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (($redirect = $this->auth->redirectIfGuest()) !== null) return $redirect;
        $result = $this->deletePlayer->delete(new DeleteSquadPlayerCommand((string) $this->route->getArgument('id'), (string) $this->currentUser->getId(), (string) $this->route->getArgument('playerId')));
        return $this->responses->createResponse($result === DeleteSquadPlayerResult::Deleted ? 204 : 422);
    }
}
