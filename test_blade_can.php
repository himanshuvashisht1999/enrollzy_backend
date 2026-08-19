<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Admin::where('role', 'Counselor')->first();
\Auth::guard('admin')->login($user);

// Render a blade string
$blade = "@can('calling-action-browse') YES @else NO @endcan";
$compiled = \Blade::compileString($blade);
ob_start();
eval('?>' . $compiled);
$output = ob_get_clean();

echo "Output of @can for logged in admin: " . trim($output) . "\n";
