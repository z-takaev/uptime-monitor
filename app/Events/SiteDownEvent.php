<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Monitor;
use App\Models\MonitorLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SiteDownEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Monitor $monitor,
        public readonly MonitorLog $monitorLog,
    ) {}
}
