<?php

declare(strict_types=1);

namespace App\Web\Identity\Register;

use App\Application\Identity\RegisterUser\RegisterUser;
use App\Application\Identity\RegisterUser\RegisterUserCommand;
use App\Application\Identity\RegisterUser\RegisterUserResult;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class RegisterAction
{
    public function __construct(
        private RegisterUser $registerUser,
        private WebViewRenderer $viewRenderer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $form = RegisterForm::fromInput($request->getParsedBody());

        if (!$form->isValid()) {
            return $this->viewRenderer->render(
                __DIR__ . '/template',
                ['form' => $form],
            )->withStatus(422);
        }

        $result = $this->registerUser->register(
            new RegisterUserCommand(
                $form->username,
                $form->email,
                $form->password,
            ),
        );

        if ($result === RegisterUserResult::Registered) {
            return $this->responseFactory
                ->createResponse(303)
                ->withHeader('Location', $this->urlGenerator->generate('home'));
        }

        $form->addBusinessError($result);

        return $this->viewRenderer
            ->render(__DIR__ . '/template', ['form' => $form])
            ->withStatus(422);
    }
}
