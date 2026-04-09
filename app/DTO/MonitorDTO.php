<?php

namespace App\DTO;

use App\Enums\CheckInterval;
use App\Http\Requests\MonitorRequest;

class MonitorDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly CheckInterval $interval,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(MonitorRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            url: $request->validated('url'),
            interval: CheckInterval::from((int) $request->validated('interval')),
            is_active: (bool) $request->validated('is_active', true),
        );
    }
}
