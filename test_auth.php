<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Admin::where('role', 'Counselor')->first();
\Auth::guard('admin')->login($user);

// Simulate what Blade does
echo "Auth::user() is " . (auth()->user() ? auth()->user()->name : 'null') . "\n";
echo "Auth::guard('admin')->user() is " . (auth()->guard('admin')->user() ? auth()->guard('admin')->user()->name : 'null') . "\n";

echo "Can institutes-browse: " . (auth()->user() && auth()->user()->can('institutes-browse') ? 'YES' : 'NO') . "\n";
