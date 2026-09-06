<?php

declare(strict_types=1);

namespace App\Web\Career\Create;

use App\Application\Career\CreateCareer\CreateCareer;
use App\Application\Career\CreateCareer\CreateCareerCommand;
use App\Application\Career\CreateCareer\CreateCareerResult;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Catalog\LeagueRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class CreateCareerAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private CurrentUser $currentUser, private CreateCareer $createCareer, private LeagueRepository $leagues, private ClubRepository $clubs, private WebViewRenderer $view, private ResponseFactoryInterface $responses, private UrlGeneratorInterface $urls) {}
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $redirect = $this->auth->redirectIfGuest();
        if ($redirect !== null) { return $redirect; }
        $form = CreateCareerForm::fromInput($request->getParsedBody());
        if ($form->isValid()) {
            $result = $this->createCareer->create(new CreateCareerCommand((string) $this->currentUser->getId(), $form->name, $form->managerName, 'FC26', $form->clubId, $form->leagueId, $form->startsOn, $form->endsOn));
            if ($result === CreateCareerResult::Created) { return $this->responses->createResponse(303)->withHeader('Location', $this->urls->generate('dashboard')); }
            $form->addBusinessError($result);
        }
        return $this->view->render(__DIR__ . '/template', ['form' => $form, 'leagues' => $this->leagues->all(), 'clubs' => $this->clubs->all()])->withStatus(422);
    }
}
