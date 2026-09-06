<?php
declare(strict_types=1);
namespace App\Application\Career\ViewCareer;
final readonly class ViewCareerCommand { public function __construct(public string $careerId, public string $userId) {} }
