<?php

namespace App\Services;

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use Carbon\Carbon;

class MonitorSchedulerService
{
    public function dispatchDueMonitors(): void
    {
        Monitor::where('is_active', true)
            ->each(function (Monitor $monitor): void {
                if ($this->isDue($monitor)) {
                    $monitor->update(['last_checked_at' => now()]);
                    CheckMonitorJob::dispatch($monitor);
                }
            });
    }

    private function isDue(Monitor $monitor): bool
    {
        if (! $monitor->last_checked_at) {
            return true;
        }

        $nextCheckAt = $monitor->last_checked_at
            ->addMinutes($monitor->interval->value);

        return Carbon::now()->greaterThanOrEqualTo($nextCheckAt);
    }
}
