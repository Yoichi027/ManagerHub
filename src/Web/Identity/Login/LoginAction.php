<?php

declare(strict_types=1);

namespace App\Web\Identity\Login;

use App\Application\Identity\AuthenticateUser\AuthenticateUser;
use App\Application\Identity\AuthenticateUser\AuthenticateUserCommand;
use App\Infrastructure\Identity\AuthenticatedUserIdentity;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class LoginAction
{
    public function __construct(
        private AuthenticateUser $authenticateUser,
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

        $form = LoginForm::fromInput($request->getParsedBody());

        if (!$form->isValid()) {
            return $this->viewRenderer->render(__DIR__ . '/template', ['form' => $form])->withStatus(422);
        }

        $user = $this->authenticateUser->authenticate(
            new AuthenticateUserCommand($form->username, $form->password),
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
