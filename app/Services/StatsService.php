<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CheckStatus;
use App\Models\Monitor;
use Illuminate\Support\Carbon;

class StatsService
{
    public function getUptime(Monitor $monitor, int $days): string
    {
        $from = Carbon::now()->subDays($days);

        $total = $monitor->monitorLogs()
            ->where('checked_at', '>=', $from)
            ->count();

        if ($total === 0) {
            return '0.00';
        }

        $upCount = $monitor->monitorLogs()
            ->where('checked_at', '>=', $from)
            ->where('status', CheckStatus::Up)
            ->count();

        return number_format(($upCount / $total) * 100, 2);
    }

    public function getAvgResponseTime(Monitor $monitor, int $days): string
    {
        $from = Carbon::now()->subDays($days);

        return number_format(
            $monitor->monitorLogs()
                ->where('checked_at', '>=', $from)
                ->where('status', CheckStatus::Up)
                ->avg('response_time_ms') ?? 0.0,
            2
        );
    }

    public function getLastIncident(Monitor $monitor): ?array
    {
        $lastDown = $monitor->monitorLogs()
            ->where('status', CheckStatus::Down)
            ->latest('checked_at')
            ->first();

        if (! $lastDown) {
            return null;
        }

        $restored = $monitor->monitorLogs()
            ->where('status', CheckStatus::Up)
            ->where('checked_at', '>', $lastDown->checked_at)
            ->oldest('checked_at')
            ->first();

        return [
            'downed_at' => $lastDown->checked_at->toIso8601String(),
            'restored_at' => $restored?->checked_at->toIso8601String(),
        ];
    }

    public function getStats(Monitor $monitor): array
    {
        return [
            'uptime' => [
                '24h' => $this->getUptime($monitor, 1),
                '7d' => $this->getUptime($monitor, 7),
                '30d' => $this->getUptime($monitor, 30),
            ],
            'avg_response_time' => [
                '24h' => $this->getAvgResponseTime($monitor, 1),
                '7d' => $this->getAvgResponseTime($monitor, 7),
                '30d' => $this->getAvgResponseTime($monitor, 30),
            ],
            'last_incident' => $this->getLastIncident($monitor),
        ];
    }
}
