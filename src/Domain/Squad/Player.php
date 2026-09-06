<?php

declare(strict_types=1);

namespace App\Domain\Squad;

use App\Domain\Shared\CalendarDate;
use App\Domain\Shared\Time\UtcInstant;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Player
{
    public readonly UuidInterface $id;
    public function __construct(UuidInterface $id, public readonly ?UuidInterface $ownerUserId, public readonly string $name, public readonly CalendarDate $birthDate, public readonly string $nationalityCode, public readonly DateTimeImmutable $createdAt, public readonly DateTimeImmutable $updatedAt, public readonly bool $isDeleted, public readonly ?DateTimeImmutable $deletedAt)
    {
        if (trim($name) === '' || mb_strlen($name) > 120) {
            throw new DomainException('Player name is invalid.');
        } if (!preg_match('/^[A-Z]{2}$/', $nationalityCode)) {
            throw new DomainException('Nationality must use ISO alpha-2.');
        } UtcInstant::assert($createdAt, $updatedAt, $deletedAt); $this->id = $id;
    } public static function create(UuidInterface $owner, string $name, CalendarDate $birth, string $country, DateTimeImmutable $at): self
    {
        return new self(Uuid::uuid7($at), $owner, trim($name), $birth, strtoupper($country), $at, $at, false, null);
    }
}
