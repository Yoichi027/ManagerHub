<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private WebViewRenderer $viewRenderer,
    ) {}

    public function __invoke(): ResponseInterface
    {
        $redirect = $this->requireAuthenticatedUser->redirectIfGuest();

        return $redirect ?? $this->viewRenderer->render(__DIR__ . '/template');
    }
}
