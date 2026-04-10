<?php

namespace App\Repositories;

use App\Enums\CheckStatus;
use App\Models\Monitor;
use App\Models\MonitorLog;

class MonitorLogRepository
{
    public function create(Monitor $monitor, array $data): MonitorLog
    {
        return $monitor->monitorLogs()->create($data);
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
}
