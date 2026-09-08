<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

$admin = Admin::first();
if ($admin) {
    Auth::guard('admin')->login($admin);
}

try {
    $request = \Illuminate\Http\Request::create('/admin/accounting/parties', 'GET');
    $response = $app->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400 && method_exists($response, 'exception') && $response->exception) {
        echo "Exception: " . $response->exception->getMessage() . "\n";
        echo "Trace: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    }
} catch (\Throwable $e) {
    echo "Caught: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
