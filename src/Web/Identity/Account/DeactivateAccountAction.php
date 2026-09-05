<?php

declare(strict_types=1);

namespace App\Web\Identity\Account;

use App\Application\Identity\DeactivateAccount\DeactivateAccount;
use App\Application\Identity\DeactivateAccount\DeactivateAccountCommand;
use App\Application\Identity\DeactivateAccount\DeactivateAccountResult;
use App\Domain\Identity\UserRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\User\CurrentUser;

final readonly class DeactivateAccountAction
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private CurrentUser $currentUser,
        private UserRepository $users,
        private DeactivateAccount $deactivateAccount,
        private AccountPage $page,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $redirect = $this->requireAuthenticatedUser->redirectIfGuest();

        if ($redirect !== null) {
            return $redirect;
        }

        $userId = Uuid::fromString((string) $this->currentUser->getId());
        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->currentUser->logout();

            return $this->requireAuthenticatedUser->redirectIfGuest();
        }

        $input = $request->getParsedBody();
        $password = is_array($input) && is_string($input['password'] ?? null) ? $input['password'] : '';
        $result = $this->deactivateAccount->deactivate(new DeactivateAccountCommand($userId, $password));

        if ($result === DeactivateAccountResult::Deactivated) {
            $this->currentUser->logout();

            return $this->responseFactory
                ->createResponse(303)
                ->withHeader('Location', $this->urlGenerator->generate('home'));
        }

        return $this->page->render(
            $user,
            deactivateError: $result === DeactivateAccountResult::PasswordIncorrect
                ? 'Password is incorrect. Your account was not deactivated.'
                : 'Your account is no longer available.',
        );
    }
}
