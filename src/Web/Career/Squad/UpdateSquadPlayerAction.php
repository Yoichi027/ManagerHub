<?php
declare(strict_types=1);
namespace App\Web\Career\Squad;
use App\Application\Squad\UpdateSquadPlayer\UpdateSquadPlayer;
use App\Application\Squad\UpdateSquadPlayer\UpdateSquadPlayerCommand;
use App\Application\Squad\UpdateSquadPlayer\UpdateSquadPlayerResult;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;
final readonly class UpdateSquadPlayerAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private CurrentRoute $route, private \Yiisoft\User\CurrentUser $currentUser, private UpdateSquadPlayer $update, private ResponseFactoryInterface $responses) {}
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (($redirect = $this->auth->redirectIfGuest()) !== null) return $redirect;
        $input = $request->getParsedBody(); if (!is_array($input)) return $this->responses->createResponse(422);
        $text = static fn (string $key): string => is_string($input[$key] ?? null) ? $input[$key] : '';
        $optionalInt = static fn (string $key): ?int => $text($key) === '' ? null : (filter_var($text($key), FILTER_VALIDATE_INT) === false ? null : (int) $text($key));
        $result = $this->update->update(new UpdateSquadPlayerCommand((string) $this->route->getArgument('id'), (string) $this->currentUser->getId(), (string) $this->route->getArgument('playerId'), $text('name'), $text('birth_date'), $text('nationality_code'), $text('position'), (int) $text('overall_initial'), (int) $text('potential_initial'), $text('value_initial'), $optionalInt('overall_current'), $optionalInt('potential_current'), ($currentValue = $text('value_current')) === '' ? null : $currentValue, $text('status')));
        return $this->responses->createResponse($result === UpdateSquadPlayerResult::Updated ? 204 : 422);
    }
}
