<?php

declare(strict_types=1);

namespace App\Web\Identity\Reactivate;

use App\Application\Identity\ReactivateAccount\ReactivateAccount;
use App\Application\Identity\ReactivateAccount\ReactivateAccountCommand;
use App\Infrastructure\Identity\AuthenticatedUserIdentity;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class ReactivateAccountAction
{
    public function __construct(
        private ReactivateAccount $reactivateAccount,
        private CurrentUser $currentUser,
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->currentUser->isGuest()) {
            return $this->redirectDashboard();
        }

        $form = ReactivationForm::fromInput($request->getParsedBody());

        if (!$form->isValid()) {
            return $this->viewRenderer->render(__DIR__ . '/template', ['form' => $form])->withStatus(422);
        }

        $user = $this->reactivateAccount->reactivate(
            new ReactivateAccountCommand($form->identifier, $form->password),
        );

        if ($user === null || !$this->currentUser->login(new AuthenticatedUserIdentity($user->id->toString()))) {
            $form->addInvalidCredentialsError();

            return $this->viewRenderer->render(__DIR__ . '/template', ['form' => $form])->withStatus(422);
        }

        return $this->redirectDashboard();
    }

    private function redirectDashboard(): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(303)
            ->withHeader('Location', $this->urlGenerator->generate('dashboard'));
    }
}
