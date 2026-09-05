<?php

declare(strict_types=1);

namespace App\Web\Identity\Account;

use App\Application\Identity\ChangePassword\ChangePassword;
use App\Application\Identity\ChangePassword\ChangePasswordCommand;
use App\Application\Identity\ChangePassword\ChangePasswordResult;
use App\Domain\Identity\UserRepository;
use App\Web\Identity\RequireAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\User\CurrentUser;

final readonly class ChangePasswordAction
{
    public function __construct(
        private RequireAuthenticatedUser $requireAuthenticatedUser,
        private CurrentUser $currentUser,
        private UserRepository $users,
        private ChangePassword $changePassword,
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
        $currentPassword = is_array($input) && is_string($input['current_password'] ?? null) ? $input['current_password'] : '';
        $newPassword = is_array($input) && is_string($input['new_password'] ?? null) ? $input['new_password'] : '';

        if ($currentPassword === '' && $newPassword === '') {
            return $this->page->render($user, passwordError: 'Enter your current password and a new password.');
        }

        if ($currentPassword === '') {
            return $this->page->render($user, passwordError: 'Enter your current password.');
        }

        if ($newPassword === '') {
            return $this->page->render($user, passwordError: 'Enter a new password.');
        }

        $result = $this->changePassword->change(new ChangePasswordCommand($userId, $currentPassword, $newPassword));

        return match ($result) {
            ChangePasswordResult::Changed => $this->page->render($this->users->findById($userId) ?? $user, success: 'Password updated.'),
            ChangePasswordResult::CurrentPasswordIncorrect => $this->page->render($user, passwordError: 'Current password is incorrect.'),
            ChangePasswordResult::InvalidPassword => $this->page->render($user, passwordError: 'Use 8–128 characters with an uppercase letter, number and symbol.'),
            ChangePasswordResult::UserNotFound => $this->page->render($user, passwordError: 'Your account is no longer available.'),
        };
    }
}
