<?php

require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = Admin::where('status', 'active')->first();
Auth::guard('admin')->setUser($admin);
Auth::setUser($admin);

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$uri = '/admin/work-management/tasks/kanban';
$request = Request::create($uri, 'GET');
$request->setUserResolver(fn() => $admin);

$session = $app->make('session')->driver();
$session->start();
$request->setLaravelSession($session);

$response = $httpKernel->handle($request);
if ($response->exception) {
    echo "Message: " . $response->exception->getMessage() . "\n";
    echo "File: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    $traces = explode("\n", $response->exception->getTraceAsString());
    for ($i = 0; $i < min(15, count($traces)); $i++) {
        echo $traces[$i] . "\n";
    }
}
