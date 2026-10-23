<?php

namespace App\Services\Counselling\WhatsApp;

use Illuminate\Support\Facades\Log;

/** For testing: writes what would be sent to the log. Never use in production. */
class LogProvider implements WhatsAppProvider
{
    public function configured(): bool { return true; }

    public function sendCode(string $phone, string $code): array
    {
        Log::info("[whatsapp:log] verification code for $phone: $code");
        return ['ok' => true];
    }

    public function sendAlert(string $phone, string $title, string $link): array
    {
        Log::info("[whatsapp:log] alert to $phone: $title $link");
        return ['ok' => true];
    }
}
