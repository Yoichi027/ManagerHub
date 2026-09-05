<?php

declare(strict_types=1);

namespace App\Web\Identity\Register;

use App\Application\Identity\RegisterUser\RegisterUserResult;

use function is_array;
use function is_string;

final class RegisterForm
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(
        public readonly string $username = '',
        public readonly string $email = '',
        public readonly string $password = '',
    ) {}

    public static function fromInput(mixed $input): self
    {
        if (!is_array($input)) {
            return new self();
        }

        return new self(
            username: self::stringValue($input['username'] ?? null),
            email: self::stringValue($input['email'] ?? null),
            password: self::stringValue($input['password'] ?? null),
        );
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if ($this->username === '') {
            $this->addError('username', 'Username is required.');
        }

        if ($this->email === '') {
            $this->addError('email', 'Email is required.');
        }

        if ($this->password === '') {
            $this->addError('password', 'Password is required.');
        }

        return $this->errors === [];
    }

    public function addBusinessError(RegisterUserResult $result): void
    {
        match ($result) {
            RegisterUserResult::UsernameTaken => $this->addError('username', 'Username is already in use.'),
            RegisterUserResult::EmailTaken => $this->addError('email', 'Email is already in use.'),
            RegisterUserResult::InvalidInput => $this->addError('general', 'Please check the registration details.'),
            RegisterUserResult::Registered => null,
        };
    }

    /** @return list<string> */
    public function errorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
