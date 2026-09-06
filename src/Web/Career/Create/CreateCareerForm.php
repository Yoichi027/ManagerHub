<?php

declare(strict_types=1);

namespace App\Web\Career\Create;

use App\Application\Career\CreateCareer\CreateCareerResult;

use function is_array;
use function is_string;

final class CreateCareerForm
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(
        public readonly string $name = '',
        public readonly string $managerName = '',
        public readonly string $clubId = '',
        public readonly string $leagueId = '',
        public readonly string $startsOn = '',
        public readonly string $endsOn = '',
    ) {}

    public static function fromInput(mixed $input): self
    {
        if (!is_array($input)) {
            return new self();
        }
        return new self(self::string($input['name'] ?? null), self::string($input['manager_name'] ?? null), self::string($input['club_id'] ?? null), self::string($input['league_id'] ?? null), self::string($input['starts_on'] ?? null), self::string($input['ends_on'] ?? null));
    }

    public function isValid(): bool
    {
        $this->errors = [];
        foreach (['name' => $this->name, 'manager_name' => $this->managerName, 'club_id' => $this->clubId, 'league_id' => $this->leagueId, 'starts_on' => $this->startsOn, 'ends_on' => $this->endsOn] as $field => $value) {
            if ($value === '') {
                $this->addError($field, 'This field is required.');
            }
        }
        return $this->errors === [];
    }

    public function addBusinessError(CreateCareerResult $result): void
    {
        match ($result) {
            CreateCareerResult::ClubNotFound => $this->addError('club_id', 'Choose a club from the available catalog.'),
            CreateCareerResult::LeagueNotFound => $this->addError('league_id', 'Choose a league from the available catalog.'),
            CreateCareerResult::InvalidInput => $this->addError('general', 'Please check the career and season details.'),
            CreateCareerResult::Created => null,
        };
    }
    /** @return list<string> */ public function errorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
