<?php

declare(strict_types=1);

use App\Models\Monitor;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns list of monitors for authenticated user', function (): void {
    $user = User::factory()->create();
    Monitor::factory()->count(3)->for($user)->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/monitors')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('returns only own monitors', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Monitor::factory()->count(2)->for($user)->create();
    Monitor::factory()->count(5)->for($other)->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/monitors')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns 401 if not authenticated', function (): void {
    $this->getJson('/api/v1/monitors')->assertStatus(401);
});

it('creates a monitor', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/monitors', [
        'name' => 'Google',
        'url' => 'https://google.com',
        'interval' => 5,
    ])->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'url', 'interval', 'is_active', 'created_at'],
        ]);

    $this->assertDatabaseHas('monitors', [
        'user_id' => $user->id,
        'url' => 'https://google.com',
    ]);
});

it('returns 422 if url is invalid', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/monitors', [
        'name' => 'Google',
        'url' => 'not-a-url',
        'interval' => 5,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

it('returns 422 if interval is invalid', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/monitors', [
        'name' => 'Google',
        'url' => 'https://google.com',
        'interval' => 7,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['interval']);
});

it('returns a monitor', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $monitor->id);
});

it('returns 404 for another users monitor', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $monitor = Monitor::factory()->for($other)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/monitors/{$monitor->id}")
        ->assertStatus(404);
});

it('updates a monitor', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->putJson("/api/v1/monitors/{$monitor->id}", [
        'name' => 'Updated Name',
        'url' => $monitor->url,
        'interval' => 10,
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.interval', 10);
});

it('returns 404 when updating another users monitor', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $monitor = Monitor::factory()->for($other)->create();
    Sanctum::actingAs($user);

    $this->putJson("/api/v1/monitors/{$monitor->id}", [
        'name' => 'Hacked',
        'url' => $monitor->url,
        'interval' => 5,
    ])->assertStatus(404);
});

it('deletes a monitor', function (): void {
    $user = User::factory()->create();
    $monitor = Monitor::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/monitors/{$monitor->id}")
        ->assertOk();

    $this->assertDatabaseMissing('monitors', ['id' => $monitor->id]);
});

it('returns 404 when deleting another users monitor', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $monitor = Monitor::factory()->for($other)->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/monitors/{$monitor->id}")
        ->assertStatus(404);
});
