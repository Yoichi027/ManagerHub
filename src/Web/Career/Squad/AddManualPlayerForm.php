<?php

declare(strict_types=1);

namespace App\Web\Career\Squad;

use App\Application\Squad\AddManualPlayer\AddManualPlayerResult;

use function is_array;
use function is_string;

final class AddManualPlayerForm
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(public readonly string $name = '', public readonly string $birthDate = '', public readonly string $nationalityCode = '', public readonly string $position = '', public readonly string $overall = '', public readonly string $potential = '', public readonly string $value = '') {}

    public static function fromInput(mixed $input): self
    {
        if (!is_array($input)) { return new self(); }
        return new self(...array_map(static fn (string $field): string => self::string($input[$field] ?? null), ['name', 'birth_date', 'nationality_code', 'position', 'overall', 'potential', 'value']));
    }
    public function isValid(): bool
    {
        $this->errors = [];
        foreach (['name' => $this->name, 'birth_date' => $this->birthDate, 'nationality_code' => $this->nationalityCode, 'position' => $this->position, 'overall' => $this->overall, 'potential' => $this->potential, 'value' => $this->value] as $field => $value) { if ($value === '') { $this->addError($field, 'This field is required.'); } }
        if ($this->overall !== '' && filter_var($this->overall, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]) === false) { $this->addError('overall', 'Use a rating from 1 to 99.'); }
        if ($this->potential !== '' && filter_var($this->potential, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]) === false) { $this->addError('potential', 'Use a rating from 1 to 99.'); }
        if ($this->value !== '' && preg_match('/^\d+(?:\.\d{1,2})?$/', $this->value) !== 1) { $this->addError('value', 'Use a non-negative amount with up to two decimal places.'); }
        return $this->errors === [];
    }
    public function addBusinessError(AddManualPlayerResult $result): void { $this->addError('general', match ($result) { AddManualPlayerResult::InvalidInput => 'Please check the player details.', AddManualPlayerResult::CareerNotFound, AddManualPlayerResult::NotOwner, AddManualPlayerResult::NoActiveSeason => 'This career is no longer available.', AddManualPlayerResult::Added => '', }); }
    /** @return list<string> */ public function errorsFor(string $field): array { return $this->errors[$field] ?? []; }
    private function addError(string $field, string $message): void { $this->errors[$field][] = $message; }
    private static function string(mixed $value): string { return is_string($value) ? $value : ''; }
}
