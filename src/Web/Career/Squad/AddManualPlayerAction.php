<?php

declare(strict_types=1);

namespace App\Web\Career\Squad;

use App\Application\Squad\AddManualPlayer\AddManualPlayer;
use App\Application\Squad\AddManualPlayer\AddManualPlayerCommand;
use App\Application\Squad\AddManualPlayer\AddManualPlayerResult;
use App\Domain\Career\CareerRepository;
use App\Domain\Season\SeasonRepository;
use App\Domain\Squad\SquadPlayerRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class AddManualPlayerAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private CurrentUser $currentUser, private CurrentRoute $route, private AddManualPlayer $addPlayer, private CareerRepository $careers, private SeasonRepository $seasons, private SquadPlayerRepository $squadPlayers, private WebViewRenderer $view, private ResponseFactoryInterface $responses, private UrlGeneratorInterface $urls) {}
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (($redirect = $this->auth->redirectIfGuest()) !== null) { return $redirect; }
        $careerId = (string) $this->route->getArgument('id'); $form = AddManualPlayerForm::fromInput($request->getParsedBody());
        if ($form->isValid()) { $rawInput = $request->getParsedBody(); $status = is_array($rawInput) && is_string($rawInput['status'] ?? null) ? $rawInput['status'] : 'Starter'; $result = $this->addPlayer->add(new AddManualPlayerCommand($careerId, (string) $this->currentUser->getId(), $form->name, $form->birthDate, $form->nationalityCode, $form->position, (int) $form->overall, (int) $form->potential, $form->value, $status)); if ($result === AddManualPlayerResult::Added) { return $this->responses->createResponse(303)->withHeader('Location', $this->urls->generate('career.squad', ['id' => $careerId]) . '?added=1'); } $form->addBusinessError($result); }
        try { $id = Uuid::fromString($careerId); $career = $this->careers->findById($id); $season = $this->seasons->findActiveByCareerId($id); } catch (\Throwable) { $career = null; $season = null; }
        if ($career === null || $season === null || !$career->userId->equals(Uuid::fromString((string) $this->currentUser->getId()))) { return $this->responses->createResponse(303)->withHeader('Location', $this->urls->generate('dashboard')); }
        return $this->view->render(__DIR__ . '/template', ['career' => $career, 'season' => $season, 'players' => $this->squadPlayers->findBySeasonId($season->id), 'form' => $form])->withStatus(422);
    }
}
