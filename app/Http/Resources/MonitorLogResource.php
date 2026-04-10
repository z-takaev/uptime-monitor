<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MonitorLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'response_code' => $this->response_code,
            'response_time_ms' => $this->response_time_ms,
            'checked_at' => $this->checked_at->toIso8601String(),
        ];
    }
}
