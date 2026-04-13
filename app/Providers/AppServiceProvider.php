<?php

namespace App\Providers;

use App\Contracts\TelegramServiceInterface;
use App\Events\SiteDownEvent;
use App\Events\SiteRestoredEvent;
use App\Listeners\SendTelegramNotificationListener;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TelegramServiceInterface::class, TelegramService::class);
    }

    public function boot(): void
    {
        Event::listen(SiteDownEvent::class, SendTelegramNotificationListener::class);
        Event::listen(SiteRestoredEvent::class, SendTelegramNotificationListener::class);
    }
}
