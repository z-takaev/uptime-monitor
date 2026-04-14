<?php

declare(strict_types=1);

use App\Enums\CheckStatus;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;
use App\Repositories\MonitorLogRepository;

it('returns latest log with specific status', function (): void {
    $monitor = Monitor::factory()->for(User::factory())->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinutes(10),
    ]);

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
        'checked_at' => now()->subMinutes(5),
    ]);

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinute(),
    ]);

    $repository = app(MonitorLogRepository::class);
    $latest = $repository->getLatestWithStatus($monitor, CheckStatus::Down);

    expect($latest)->not->toBeNull()
        ->and($latest->status)->toBe(CheckStatus::Down);
});

it('returns null when no log with specific status exists', function (): void {
    $monitor = Monitor::factory()->for(User::factory())->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
    ]);

    $repository = app(MonitorLogRepository::class);
    $result = $repository->getLatestWithStatus($monitor, CheckStatus::Down);

    expect($result)->toBeNull();
});

it('returns paginated logs for monitor', function (): void {
    $monitor = Monitor::factory()->for(User::factory())->create();
    MonitorLog::factory()->for($monitor)->count(25)->create();

    $repository = app(MonitorLogRepository::class);
    $paginated = $repository->getPaginatedForMonitor($monitor, 10);

    expect($paginated->count())->toBe(10)
        ->and($paginated->total())->toBe(25);
});
