<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\MonitorDTO;
use App\Models\Monitor;
use App\Repositories\MonitorRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final class MonitorService
{
    public function __construct(
        private readonly MonitorRepository $repository,
    ) {}

    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllForUser($userId, $perPage);
    }

    public function create(int $userId, MonitorDTO $dto): Monitor
    {
        return $this->repository->create($userId, $dto);
    }

    public function update(Monitor $monitor, MonitorDTO $dto): Monitor
    {
        return $this->repository->update($monitor, $dto);
    }

    public function delete(Monitor $monitor): void
    {
        $this->repository->delete($monitor);
    }
}
