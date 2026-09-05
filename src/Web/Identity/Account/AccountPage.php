<?php

declare(strict_types=1);

namespace App\Web\Identity\Account;

use App\Domain\Identity\User;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class AccountPage
{
    public function __construct(private WebViewRenderer $viewRenderer) {}

    public function render(
        User $user,
        ?string $emailError = null,
        ?string $passwordError = null,
        ?string $deactivateError = null,
        ?string $success = null,
    ): ResponseInterface {
        return $this->viewRenderer->render(__DIR__ . '/template', compact(
            'user',
            'emailError',
            'passwordError',
            'deactivateError',
            'success',
        ));
    }
}
