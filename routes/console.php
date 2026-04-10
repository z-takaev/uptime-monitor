<?php

use App\Services\MonitorSchedulerService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (): void {
    app(MonitorSchedulerService::class)->dispatchDueMonitors();
})->everyMinute();
