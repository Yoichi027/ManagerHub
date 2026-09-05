<?php

declare(strict_types=1);

namespace App\Web\Identity\Login;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class ShowLoginFormAction
{
    public function __construct(private WebViewRenderer $viewRenderer) {}

    public function __invoke(): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', ['form' => new LoginForm()]);
    }
}
