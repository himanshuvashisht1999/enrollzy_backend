<?php

namespace App\Services\Counselling\WhatsApp;

/**
 * WhatsApp needs a Business API account, pre-approved templates and per-message cost, so nothing is sent
 * until WHATSAPP_DRIVER is set. Messages only go to numbers that verified by code AND opted in explicitly.
 */
interface WhatsAppProvider
{
    public function configured(): bool;

    /** @return array{ok: bool, error?: string} */
    public function sendCode(string $phone, string $code): array;

    /** @return array{ok: bool, error?: string} */
    public function sendAlert(string $phone, string $title, string $link): array;
}
