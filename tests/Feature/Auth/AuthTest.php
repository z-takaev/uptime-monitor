<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('registers a new user and returns token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'created_at'],
            'token',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

it('returns 422 if email already taken', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ])->assertStatus(422);
});

it('returns 422 if required fields are missing', function (): void {
    $this->postJson('/api/v1/auth/register', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('logs in with correct credentials and returns token', function (): void {
    $user = User::factory()->create(['password' => 'password123']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email'],
            'token',
        ]);
});

it('returns 401 with wrong password', function (): void {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ])->assertStatus(401);
});

it('replaces old tokens on login', function (): void {
    $user = User::factory()->create(['password' => 'password123']);
    $user->createToken('old-token');

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertOk();

    $this->assertCount(1, $user->fresh()->tokens);
});

it('logs out and deletes current token', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/logout')->assertOk();

    $this->assertCount(0, $user->fresh()->tokens);
});

it('returns 401 on logout without token', function (): void {
    $this->postJson('/api/v1/auth/logout')->assertStatus(401);
});
