<?php

declare(strict_types=1);

use App\Enums\CheckStatus;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns paginated history for monitor', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();
    MonitorLog::factory()->for($monitor)->count(5)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/history")
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('returns 404 for history of another users monitor', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $monitor = Monitor::factory()->for($other)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/history")
        ->assertStatus(404);
});

it('returns stats for monitor', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/stats")
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'uptime' => ['24h', '7d', '30d'],
                'avg_response_time' => ['24h', '7d', '30d'],
                'last_incident',
            ],
        ]);
});

it('calculates uptime correctly', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();

    // 3 Up и 1 Down = 75% uptime
    MonitorLog::factory()->for($monitor)->count(3)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subHours(2),
    ]);
    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
        'checked_at' => now()->subHour(),
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/stats")
        ->assertOk()
        ->assertJsonPath('data.uptime.24h', '75.00');
});

it('returns last incident when site was down', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();

    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Down,
        'checked_at' => now()->subHours(2),
    ]);
    MonitorLog::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subHour(),
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/stats")
        ->assertOk()
        ->assertJsonPath('data.last_incident.downed_at', fn ($value) => ! is_null($value))
        ->assertJsonPath('data.last_incident.restored_at', fn ($value) => ! is_null($value));
});

it('returns 404 for stats of another users monitor', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $monitor = Monitor::factory()->for($other)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}/stats")
        ->assertStatus(404);
});
