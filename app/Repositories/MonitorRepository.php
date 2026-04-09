<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\MonitorDTO;
use App\Models\Monitor;
use Illuminate\Pagination\LengthAwarePaginator;

final class MonitorRepository
{
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Monitor::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(int $id, int $userId): ?Monitor
    {
        return Monitor::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(int $userId, MonitorDTO $dto): Monitor
    {
        return Monitor::create([
            'user_id' => $userId,
            'name' => $dto->name,
            'url' => $dto->url,
            'interval' => $dto->interval,
            'is_active' => $dto->is_active,
        ]);
    }

    public function update(Monitor $monitor, MonitorDTO $dto): Monitor
    {
        $monitor->update([
            'name' => $dto->name,
            'url' => $dto->url,
            'interval' => $dto->interval,
            'is_active' => $dto->is_active,
        ]);

        return $monitor->fresh();
    }

    public function delete(Monitor $monitor): void
    {
        $monitor->delete();
    }
}
