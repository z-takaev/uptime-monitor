<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TelegramServiceInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TelegramService implements TelegramServiceInterface
{
    private string $token;

    private string $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendMessage(string $text): void
    {
        if (empty($this->token) || empty($this->chatId)) {
            Log::warning('Telegram not configured, skipping notification');

            return;
        }

        try {
            Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (Exception $e) {
            Log::error('Telegram notification failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
