<?php

declare(strict_types=1);

namespace App\Web\Identity\Login;

use function is_array;
use function is_string;

final class LoginForm
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(
        public readonly string $username = '',
        public readonly string $password = '',
    ) {}

    public static function fromInput(mixed $input): self
    {
        if (!is_array($input)) {
            return new self();
        }

        return new self(
            self::stringValue($input['username'] ?? null),
            self::stringValue($input['password'] ?? null),
        );
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if ($this->username === '') {
            $this->addError('username', 'Username is required.');
        }

        if ($this->password === '') {
            $this->addError('password', 'Password is required.');
        }

        return $this->errors === [];
    }

    public function addInvalidCredentialsError(): void
    {
        $this->addError('general', 'Username or password is incorrect.');
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
