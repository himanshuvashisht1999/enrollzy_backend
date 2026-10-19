<?php

namespace App\Services\Counselling\WhatsApp;

use Illuminate\Support\Facades\Http;

/**
 * Meta WhatsApp Cloud API. Needs WHATSAPP_TOKEN and WHATSAPP_PHONE_NUMBER_ID, and two APPROVED templates
 * (config cns.whatsapp.templates): "otp" with one body parameter (the code) and "alert" with two (title, link).
 */
class CloudApiProvider implements WhatsAppProvider
{
    public function configured(): bool
    {
        return filled(config('cns.whatsapp.token')) && filled(config('cns.whatsapp.phone_number_id'));
    }

    public function sendCode(string $phone, string $code): array
    {
        return $this->template($phone, config('cns.whatsapp.templates.otp'), [$code]);
    }

    public function sendAlert(string $phone, string $title, string $link): array
    {
        return $this->template($phone, config('cns.whatsapp.templates.alert'), [$title, $link]);
    }

    private function template(string $phone, string $name, array $params): array
    {
        if (!$this->configured()) {
            return ['ok' => false, 'error' => 'WhatsApp credentials are missing.'];
        }
        $url = 'https://graph.facebook.com/' . config('cns.whatsapp.api_version') . '/' . config('cns.whatsapp.phone_number_id') . '/messages';
        try {
            $res = Http::withToken(config('cns.whatsapp.token'))->timeout(15)->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => ltrim($phone, '+'),
                'type' => 'template',
                'template' => ['name' => $name, 'language' => ['code' => config('cns.whatsapp.language')],
                    'components' => [['type' => 'body', 'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $params)]]],
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'WhatsApp request failed: ' . $e->getMessage()];
        }
        return $res->successful() ? ['ok' => true] : ['ok' => false, 'error' => 'WhatsApp API error ' . $res->status() . ': ' . \Illuminate\Support\Str::limit($res->body(), 200)];
    }
}
