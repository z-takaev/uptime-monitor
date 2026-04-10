<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CheckStatus;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

final class MonitorLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'monitor_id' => Monitor::factory(),
            'status' => $this->faker->randomElement(CheckStatus::cases()),
            'response_code' => $this->faker->randomElement([200, 301, 404, 500]),
            'response_time_ms' => $this->faker->numberBetween(50, 2000),
            'checked_at' => now(),
        ];
    }
}
