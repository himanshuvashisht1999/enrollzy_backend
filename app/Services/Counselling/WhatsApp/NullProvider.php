<?php

namespace App\Services\Counselling\WhatsApp;

/** Default: WhatsApp is not set up, so deliveries are marked skipped instead of pretending to be sent. */
class NullProvider implements WhatsAppProvider
{
    public function configured(): bool { return false; }
    public function sendCode(string $phone, string $code): array { return ['ok' => false, 'error' => 'WhatsApp provider is not configured.']; }
    public function sendAlert(string $phone, string $title, string $link): array { return ['ok' => false, 'error' => 'WhatsApp provider is not configured.']; }
}
