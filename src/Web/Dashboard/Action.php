<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Domain\Career\CareerRepository;
use App\Domain\Catalog\ClubRepository;
use App\Domain\Season\SeasonRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private CurrentUser $currentUser,
        private CareerRepository $careers,
        private SeasonRepository $seasons,
        private ClubRepository $clubs,
        private WebViewRenderer $viewRenderer,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $redirect = $this->requireAuthenticatedUser->redirectIfGuest();

        if ($redirect !== null) {
            return $redirect;
        }

        $workspaceCareers = [];
        foreach ($this->careers->findByUserId(Uuid::fromString((string) $this->currentUser->getId())) as $career) {
            $season = $this->seasons->findActiveByCareerId($career->id);
            if ($season === null) {
                continue;
            }
            $club = $season->managedClub->id === null ? null : $this->clubs->findById($season->managedClub->id);
            $workspaceCareers[] = [
                'career_id' => $career->id->toString(),
                'career_name' => $career->name->value,
                'manager_name' => $career->managerName->value,
                'game_edition' => $career->gameEdition->value,
                'club_name' => $season->managedClub->name,
                'club_logo_url' => $club?->logoUrl,
                'season_label' => $season->label->value,
            ];
        }

        $query = $request->getQueryParams();
        return $this->viewRenderer->render(__DIR__ . '/template', [
            'careers' => $workspaceCareers,
            'success' => ($query['deleted'] ?? null) === '1' ? 'Career deleted.' : null,
            'deleteError' => match ($query['delete_error'] ?? null) {
                'confirmation' => 'Confirm the deletion before continuing.',
                'unavailable' => 'This career is no longer available.',
                default => null,
            },
        ]);
    }
}
