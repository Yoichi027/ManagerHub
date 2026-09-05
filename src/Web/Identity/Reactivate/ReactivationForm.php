<?php

declare(strict_types=1);

namespace App\Web\Identity\Reactivate;

use function is_array;
use function is_string;

final class ReactivationForm
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(
        public readonly string $identifier = '',
        public readonly string $password = '',
    ) {}

    public static function fromInput(mixed $input): self
    {
        if (!is_array($input)) {
            return new self();
        }

        return new self(
            is_string($input['identifier'] ?? null) ? $input['identifier'] : '',
            is_string($input['password'] ?? null) ? $input['password'] : '',
        );
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if ($this->identifier === '') {
            $this->addError('identifier', 'Username or email is required.');
        }

        if ($this->password === '') {
            $this->addError('password', 'Password is required.');
        }

        return $this->errors === [];
    }

    public function addInvalidCredentialsError(): void
    {
        $this->addError('general', 'The account could not be reactivated with these credentials.');
    }

    /** @return list<string> */
    public function errorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
