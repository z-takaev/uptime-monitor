<?php

declare(strict_types=1);

use App\Contracts\TelegramServiceInterface;
use App\Enums\CheckStatus;
use App\Events\SiteDownEvent;
use App\Events\SiteRestoredEvent;
use App\Listeners\SendTelegramNotificationListener;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;

it('sends telegram notification when site goes down', function (): void {
    $telegram = Mockery::mock(TelegramServiceInterface::class);
    $telegram->shouldReceive('sendMessage')
        ->once()
        ->with(Mockery::on(fn ($msg) => str_contains($msg, 'недоступен')));

    app()->instance(TelegramServiceInterface::class, $telegram);

    $monitor = Monitor::factory()->for(User::factory())->create();
    $log = MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
    ]);

    $listener = app(SendTelegramNotificationListener::class);
    $listener->handle(new SiteDownEvent($monitor, $log));
});

it('sends telegram notification when site is restored', function (): void {
    $telegram = Mockery::mock(TelegramServiceInterface::class);
    $telegram->shouldReceive('sendMessage')
        ->once()
        ->with(Mockery::on(fn ($msg) => str_contains($msg, 'восстановлен')));

    app()->instance(TelegramServiceInterface::class, $telegram);

    $monitor = Monitor::factory()->for(User::factory())->create();
    $log = MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
    ]);

    $listener = app(SendTelegramNotificationListener::class);
    $listener->handle(new SiteRestoredEvent($monitor, $log));
});

it('does not throw exception when telegram is not configured', function (): void {
    config(['services.telegram.token' => '']);
    config(['services.telegram.chat_id' => '']);

    $monitor = Monitor::factory()->for(User::factory())->create();
    $log = MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
    ]);

    expect(fn () => event(new SiteDownEvent($monitor, $log)))
        ->not->toThrow(Exception::class);
});
