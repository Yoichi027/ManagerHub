<?php

declare(strict_types=1);

namespace App\Web\Career\Create;

use App\Domain\Catalog\ClubRepository;
use App\Domain\Catalog\LeagueRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class ShowCreateCareerFormAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private LeagueRepository $leagues, private ClubRepository $clubs, private WebViewRenderer $view) {}
    public function __invoke(): ResponseInterface
    {
        $redirect = $this->auth->redirectIfGuest();
        if ($redirect !== null) {
            return $redirect;
        }
        $leagues = $this->leagues->all();
        $form = new CreateCareerForm(startsOn: '2025-07-01', endsOn: '2026-06-30');
        return $this->view->render(__DIR__ . '/template', ['form' => $form, 'leagues' => $leagues, 'clubs' => $this->clubs->all()]);
    }
}
