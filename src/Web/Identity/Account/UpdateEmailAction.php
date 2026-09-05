<?php

declare(strict_types=1);

namespace App\Web\Identity\Account;

use App\Application\Identity\ChangeEmail\ChangeEmail;
use App\Application\Identity\ChangeEmail\ChangeEmailCommand;
use App\Application\Identity\ChangeEmail\ChangeEmailResult;
use App\Domain\Identity\UserRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\User\CurrentUser;

final readonly class UpdateEmailAction
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private CurrentUser $currentUser,
        private UserRepository $users,
        private ChangeEmail $changeEmail,
        private AccountPage $page,
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
        $email = is_array($input) && is_string($input['email'] ?? null) ? $input['email'] : '';
        $result = $this->changeEmail->change(new ChangeEmailCommand($userId, $email));

        return match ($result) {
            ChangeEmailResult::Changed => $this->page->render($this->users->findById($userId) ?? $user, success: 'Email address updated.'),
            ChangeEmailResult::EmailTaken => $this->page->render($user, emailError: 'This email address is already in use.'),
            ChangeEmailResult::InvalidInput => $this->page->render($user, emailError: 'Enter a valid email address.'),
            ChangeEmailResult::UserNotFound => $this->page->render($user, emailError: 'Your account is no longer available.'),
        };
    }
}
