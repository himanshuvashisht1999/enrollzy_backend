<?php

namespace App\Console\Commands;

use App\Services\Counselling\Alerts\AlertScanner;
use App\Services\Counselling\Alerts\AlertSender;
use Illuminate\Console\Command;

class DispatchCounsellingAlerts extends Command
{
    protected $signature = 'cns:dispatch-alerts {--scan-only : only create alerts} {--send-only : only deliver queued alerts}';
    protected $description = 'Create alerts from newly verified exam / counselling data and deliver them to confirmed followers.';

    public function handle(AlertScanner $scanner, AlertSender $sender): int
    {
        if (!$this->option('send-only')) {
            $this->info($scanner->scan() . ' new alert(s) created.');
        }
        if (!$this->option('scan-only')) {
            $r = $sender->send();
            $this->info("Deliveries: {$r['sent']} sent, {$r['failed']} failed, {$r['skipped']} skipped.");
        }
        return self::SUCCESS;
    }
}
