<?php

namespace App\Services\Counselling\WhatsApp;

class WhatsApp
{
    public static function provider(): WhatsAppProvider
    {
        return match (config('cns.whatsapp.driver')) {
            'log' => new LogProvider(),
            'cloud' => new CloudApiProvider(),
            default => new NullProvider(),
        };
    }
}
