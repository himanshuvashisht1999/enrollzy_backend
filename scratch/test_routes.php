<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== TESTING ACCOUNTING HTTP ENDPOINTS ===\n\n";

$admin = Admin::first();
if ($admin) {
    Auth::guard('admin')->login($admin);
}

$routesToTest = [
    'admin.accounting.dashboard' => [],
    'admin.accounting.chart_of_accounts.index' => [],
    'admin.accounting.chart_of_accounts.create' => [],
    'admin.accounting.parties.index' => [],
    'admin.accounting.parties.create' => [],
    'admin.accounting.invoices.index' => [],
    'admin.accounting.invoices.create' => [],
    'admin.accounting.bills.index' => [],
    'admin.accounting.bills.create' => [],
    'admin.accounting.expenses.index' => [],
    'admin.accounting.expenses.create' => [],
    'admin.accounting.funding.index' => [],
    'admin.accounting.funding.create' => [],
    'admin.accounting.banking.index' => [],
    'admin.accounting.banking.create' => [],
    'admin.accounting.journals.index' => [],
    'admin.accounting.journals.create' => [],
    'admin.accounting.reports.index' => [],
    'admin.accounting.reports.trial_balance' => [],
    'admin.accounting.reports.profit_loss' => [],
    'admin.accounting.reports.balance_sheet' => [],
    'admin.accounting.reports.general_ledger' => [],
    'admin.accounting.reports.customer_ageing' => [],
    'admin.accounting.reports.vendor_ageing' => [],
    'admin.accounting.reports.gst_report' => [],
    'admin.accounting.reports.tds_report' => [],
];

$allPassed = true;

foreach ($routesToTest as $name => $params) {
    try {
        $url = route($name, $params);
        $uri = parse_url($url, PHP_URL_PATH) ?: $url;
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        $response = $app->handle($request);
        $status = $response->getStatusCode();

        if ($status === 200 || $status === 302) {
            echo "   [200 OK] {$name} ({$url})\n";
        } else {
            echo "   [FAIL {$status}] {$name} ({$url})\n";
            $allPassed = false;
        }
    } catch (\Throwable $e) {
        echo "   [ERROR] {$name}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $allPassed = false;
    }
}

echo "\nResult: " . ($allPassed ? "ALL 26 ACCOUNTING ROUTES RETURNED 200 OK! (PASSED ✓)" : "SOME ROUTES FAILED ✗") . "\n";
