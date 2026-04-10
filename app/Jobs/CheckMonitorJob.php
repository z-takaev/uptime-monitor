<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CheckStatus;
use App\Events\SiteDownEvent;
use App\Events\SiteRestoredEvent;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Repositories\MonitorLogRepository;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CheckMonitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        private readonly Monitor $monitor,
    ) {}

    public function handle(MonitorLogRepository $repository): void
    {
        $previousLog = $repository->getLatest($this->monitor);

        $startTime = microtime(true);

        try {
            $response = Http::timeout(10)->get($this->monitor->url);

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            $isUp = $response->successful();

            $monitorLog = $repository->create($this->monitor, [
                'status' => $isUp ? CheckStatus::Up : CheckStatus::Down,
                'response_code' => $response->status(),
                'response_time_ms' => $responseTimeMs,
                'checked_at' => now(),
            ]);

        } catch (Exception $e) {
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $monitorLog = $repository->create($this->monitor, [
                'status' => CheckStatus::Down,
                'response_code' => null,
                'response_time_ms' => $responseTimeMs,
                'checked_at' => now(),
            ]);
        }

        $this->dispatchEvents($monitorLog->status, $previousLog);
    }

    private function dispatchEvents(CheckStatus $currentStatus, ?MonitorLog $previousLog): void
    {
        if (! $previousLog) {
            return;
        }

        if ($currentStatus === CheckStatus::Down && $previousLog->status === CheckStatus::Up) {
            SiteDownEvent::dispatch($this->monitor, $previousLog);
        }

        if ($currentStatus === CheckStatus::Up && $previousLog->status === CheckStatus::Down) {
            SiteRestoredEvent::dispatch($this->monitor, $previousLog);
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('CheckMonitorJob failed', [
            'monitor_id' => $this->monitor->id,
            'error' => $e->getMessage(),
        ]);
    }
}
