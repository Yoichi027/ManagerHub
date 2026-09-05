<?php

declare(strict_types=1);

namespace App\Web\Identity\Login;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class ShowLoginFormAction
{
    public function __construct(
        private CurrentUser $currentUser,
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(): ResponseInterface
    {
        if (!$this->currentUser->isGuest()) {
            return $this->responseFactory
                ->createResponse(303)
                ->withHeader('Location', $this->urlGenerator->generate('dashboard'));
        }

        return $this->viewRenderer->render(__DIR__ . '/template', ['form' => new LoginForm()]);
    }
}
