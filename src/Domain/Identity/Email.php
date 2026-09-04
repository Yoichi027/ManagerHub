<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use DomainException;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;

final readonly class Email
{
    public string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            throw new DomainException('Email cannot be empty.');
        }

        if (strlen($value) > 255) {
            throw new DomainException('Email cannot exceed 255 bytes.');
        }

        $validator = new EmailValidator();

        if (!$validator->isValid($value, new NoRFCWarningsValidation())) {
            throw new DomainException('Email must be syntactically valid.');
        }

        $this->value = $value;
    }
}
