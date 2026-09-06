<?php
declare(strict_types=1);
namespace App\Application\Career\ViewCareer;
use App\Application\Shared\Time\UtcClock;
use App\Domain\Career\CareerRepository;
use Ramsey\Uuid\Uuid;
final readonly class ViewCareer { public function __construct(private CareerRepository $careers, private UtcClock $clock) {} public function view(ViewCareerCommand $command): void { try { $id = Uuid::fromString($command->careerId); $userId = Uuid::fromString($command->userId); } catch (\Throwable) { return; } $career = $this->careers->findById($id); if ($career === null || !$career->userId->equals($userId)) return; $career->viewed($this->clock->now()); $this->careers->save($career); } }
