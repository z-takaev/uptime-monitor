<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CheckInterval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class MonitorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->domainName(),
            'url' => $this->faker->url(),
            'interval' => $this->faker->randomElement(CheckInterval::values()),
            'is_active' => true,
        ];
    }
}
