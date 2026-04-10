<?php

declare(strict_types=1);

use App\Enums\CheckStatus;
use App\Events\SiteDownEvent;
use App\Events\SiteRestoredEvent;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;
use App\Repositories\MonitorLogRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

// ───── Успешная проверка ─────

it('saves successful check result when site is up', function (): void {
    Http::fake([
        '*' => Http::response('OK', 200),
    ]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    $log = MonitorLog::where('monitor_id', $monitor->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(CheckStatus::Up)
        ->and($log->response_code)->toBe(200)
        ->and($log->response_time_ms)->toBeGreaterThan(0);
});

it('saves failed check result when site is down', function (): void {
    Http::fake([
        '*' => Http::response('Error', 500),
    ]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    $log = MonitorLog::where('monitor_id', $monitor->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(CheckStatus::Down)
        ->and($log->response_code)->toBe(500);
});

it('saves failed check result when site is unreachable', function (): void {
    Http::fake([
        '*' => fn () => throw new Exception('Connection refused'),
    ]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    $log = MonitorLog::where('monitor_id', $monitor->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(CheckStatus::Down)
        ->and($log->response_code)->toBeNull();
});

it('updates last_checked_at on monitor after check', function (): void {
    Http::fake(['*' => Http::response('OK', 200)]);

    $monitor = Monitor::factory()->for(User::factory())->create([
        'last_checked_at' => null,
    ]);

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    expect(MonitorLog::where('monitor_id', $monitor->id)->count())->toBe(1);
});

// ───── События ─────

it('dispatches SiteDownEvent when site goes down', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response('Error', 500)]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinute(),
    ]);

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    Event::assertDispatched(SiteDownEvent::class);
});

it('dispatches SiteRestoredEvent when site comes back up', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response('OK', 200)]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
        'checked_at' => now()->subMinute(),
    ]);

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    Event::assertDispatched(SiteRestoredEvent::class);
});

it('does not dispatch events when status has not changed', function (): void {
    Event::fake();
    Http::fake(['*' => Http::response('OK', 200)]);

    $monitor = Monitor::factory()->for(User::factory())->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinute(),
    ]);

    (new CheckMonitorJob($monitor))->handle(
        app(MonitorLogRepository::class)
    );

    Event::assertNotDispatched(SiteDownEvent::class);
    Event::assertNotDispatched(SiteRestoredEvent::class);
});
