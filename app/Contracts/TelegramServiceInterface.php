<?php

declare(strict_types=1);

namespace App\Contracts;

interface TelegramServiceInterface
{
    public function sendMessage(string $text): void;
}
