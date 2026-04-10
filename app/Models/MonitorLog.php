<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MonitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitor_id', 'checked_at',
        'status', 'response_code', 'response_time_ms',
    ];

    protected $casts = [
        'status' => CheckStatus::class,
        'checked_at' => 'datetime',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }
}
