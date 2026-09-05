<?php

declare(strict_types=1);

namespace App\Web\Identity;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;

final readonly class LogoutAction
{
    public function __construct(
        private CurrentUser $currentUser,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(): ResponseInterface
    {
        $this->currentUser->logout();

        return $this->responseFactory
            ->createResponse(303)
            ->withHeader('Location', $this->urlGenerator->generate('home'));
    }
}
