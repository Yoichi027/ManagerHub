<?php

declare(strict_types=1);

namespace App\Web\Career;

use App\Application\Career\DeleteCareer\DeleteCareer;
use App\Application\Career\DeleteCareer\DeleteCareerCommand;
use App\Application\Career\DeleteCareer\DeleteCareerResult;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;

final readonly class DeleteCareerAction
{
    public function __construct(private RequireAuthenticatedUser $auth, private CurrentUser $currentUser, private CurrentRoute $route, private DeleteCareer $deleteCareer, private ResponseFactoryInterface $responses, private UrlGeneratorInterface $urls) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $redirect = $this->auth->redirectIfGuest();
        if ($redirect !== null) { return $redirect; }
        $input = $request->getParsedBody();
        if (!is_array($input) || ($input['confirm_delete'] ?? null) !== '1') {
            return $this->redirect('delete_error=confirmation');
        }
        $result = $this->deleteCareer->delete(new DeleteCareerCommand((string) $this->route->getArgument('id'), (string) $this->currentUser->getId()));
        return $this->redirect($result === DeleteCareerResult::Deleted ? 'deleted=1' : 'delete_error=unavailable');
    }

    private function redirect(string $query): ResponseInterface
    {
        return $this->responses->createResponse(303)->withHeader('Location', $this->urls->generate('dashboard') . '?' . $query);
    }
}
