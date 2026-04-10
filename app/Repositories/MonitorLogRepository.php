<?php

namespace App\Repositories;

use App\DTO\MonitorLogDTO;
use App\Enums\CheckStatus;
use App\Models\Monitor;
use App\Models\MonitorLog;
use Illuminate\Pagination\LengthAwarePaginator;

class MonitorLogRepository
{
    public function create(Monitor $monitor, MonitorLogDTO $dto): MonitorLog
    {
        return $monitor->monitorLogs()->create([
            'status' => $dto->status,
            'response_code' => $dto->response_code,
            'response_time_ms' => $dto->response_time_ms,
            'checked_at' => $dto->checked_at,
        ]);
    }

    public function getLatest(Monitor $monitor): ?MonitorLog
    {
        return $monitor->monitorLogs()
            ->latest('checked_at')
            ->first();
    }

    public function getLatestWithStatus(Monitor $monitor, CheckStatus $status): ?MonitorLog
    {
        return $monitor->monitorLogs()
            ->where('status', $status)
            ->latest('checked_at')
            ->first();
    }

    public function getPaginatedForMonitor(Monitor $monitor, int $perPage = 20): LengthAwarePaginator
    {
        return $monitor->monitorLogs()
            ->latest('checked_at')
            ->paginate($perPage);
    }
}
