<?php

declare(strict_types=1);

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\User;
use App\Services\MonitorSchedulerService;
use Illuminate\Support\Facades\Queue;

it('dispatches job for active monitor that has never been checked', function (): void {
    Queue::fake();

    Monitor::factory()->for(User::factory())->create([
        'is_active' => true,
        'last_checked_at' => null,
    ]);

    app(MonitorSchedulerService::class)->dispatchDueMonitors();

    Queue::assertPushed(CheckMonitorJob::class);
});

it('dispatches job for monitor whose interval has passed', function (): void {
    Queue::fake();

    Monitor::factory()->for(User::factory())->create([
        'is_active' => true,
        'interval' => 5,
        'last_checked_at' => now()->subMinutes(6),
    ]);

    app(MonitorSchedulerService::class)->dispatchDueMonitors();

    Queue::assertPushed(CheckMonitorJob::class);
});

it('does not dispatch job for monitor whose interval has not passed', function (): void {
    Queue::fake();

    Monitor::factory()->for(User::factory())->create([
        'is_active' => true,
        'interval' => 5,
        'last_checked_at' => now()->subMinutes(2),
    ]);

    app(MonitorSchedulerService::class)->dispatchDueMonitors();

    Queue::assertNotPushed(CheckMonitorJob::class);
});

it('does not dispatch job for inactive monitor', function (): void {
    Queue::fake();

    Monitor::factory()->for(User::factory())->create([
        'is_active' => false,
        'last_checked_at' => null,
    ]);

    app(MonitorSchedulerService::class)->dispatchDueMonitors();

    Queue::assertNotPushed(CheckMonitorJob::class);
});
