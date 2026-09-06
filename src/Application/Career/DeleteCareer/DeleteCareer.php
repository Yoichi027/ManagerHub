<?php

declare(strict_types=1);

namespace App\Application\Career\DeleteCareer;

use App\Application\Shared\Time\UtcClock;
use App\Domain\Career\CareerRepository;
use Ramsey\Uuid\Uuid;

final readonly class DeleteCareer
{
    public function __construct(private CareerRepository $careers, private UtcClock $clock) {}

    public function delete(DeleteCareerCommand $command): DeleteCareerResult
    {
        try {
            $careerId = Uuid::fromString($command->careerId);
            $userId = Uuid::fromString($command->userId);
        } catch (\Throwable) {
            return DeleteCareerResult::InvalidInput;
        }
        $career = $this->careers->findById($careerId);
        if ($career === null) {
            return DeleteCareerResult::NotFound;
        }
        if (!$career->userId->equals($userId)) {
            return DeleteCareerResult::NotOwner;
        }
        $career->delete($this->clock->now());
        $this->careers->save($career);
        return DeleteCareerResult::Deleted;
    }
}
