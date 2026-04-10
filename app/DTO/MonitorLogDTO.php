<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\CheckStatus;
use Illuminate\Support\Carbon;

final class MonitorLogDTO
{
    public function __construct(
        public readonly CheckStatus $status,
        public readonly ?int $response_code,
        public readonly int $response_time_ms,
        public readonly Carbon $checked_at,
    ) {}
}
