<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\TelegramServiceInterface;
use App\Events\SiteDownEvent;
use App\Events\SiteRestoredEvent;

final class SendTelegramNotificationListener
{
    public function __construct(
        private readonly TelegramServiceInterface $telegram,
    ) {}

    public function handle(SiteDownEvent|SiteRestoredEvent $event): void
    {
        $message = $event instanceof SiteDownEvent
            ? $this->buildDownMessage($event)
            : $this->buildRestoredMessage($event);

        $this->telegram->sendMessage($message);
    }

    private function buildDownMessage(SiteDownEvent $event): string
    {
        return sprintf(
            "🔴 <b>Сайт недоступен</b>\n\n".
            "<b>%s</b>\n".
            "%s\n".
            "Код ответа: %s\n".
            "Время отклика: %sms\n".
            '%s',
            $event->monitor->name,
            $event->monitor->url,
            $event->monitorLog->response_code ?? 'нет ответа',
            $event->monitorLog->response_time_ms,
            $event->monitorLog->checked_at->format('d.m.Y H:i:s'),
        );
    }

    private function buildRestoredMessage(SiteRestoredEvent $event): string
    {
        return sprintf(
            "🟢 <b>Сайт восстановлен</b>\n\n".
            "<b>%s</b>\n".
            "%s\n".
            "Время отклика: %sms\n".
            '%s',
            $event->monitor->name,
            $event->monitor->url,
            $event->monitorLog->response_time_ms,
            $event->monitorLog->checked_at->format('d.m.Y H:i:s'),
        );
    }
}
