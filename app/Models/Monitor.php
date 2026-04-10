<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name', 'url',
        'interval', 'is_active',
        'last_checked_at',
    ];

    protected $casts = [
        'interval' => CheckInterval::class,
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monitorLogs(): HasMany
    {
        return $this->hasMany(MonitorLog::class);
    }
}
