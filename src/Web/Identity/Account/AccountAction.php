<?php

declare(strict_types=1);

namespace App\Web\Identity\Account;

use App\Domain\Identity\UserRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\User\CurrentUser;

final readonly class AccountAction
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private CurrentUser $currentUser,
        private UserRepository $users,
        private AccountPage $page,
    ) {}

    public function __invoke(): ResponseInterface
    {
        $redirect = $this->requireAuthenticatedUser->redirectIfGuest();

        if ($redirect !== null) {
            return $redirect;
        }

        $user = $this->users->findById(Uuid::fromString((string) $this->currentUser->getId()));

        if ($user === null) {
            $this->currentUser->logout();

            return $this->requireAuthenticatedUser->redirectIfGuest();
        }

        return $this->page->render($user);
    }
}
