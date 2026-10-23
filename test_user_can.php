<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Admin::where('role', 'Counselor')->first();
\Auth::guard('admin')->login($user);

// Let's test the specific user->can check
echo "Can institutes-browse: " . ($user && $user->can('institutes-browse') ? 'YES' : 'NO') . "\n";
echo "Can calling-action-browse: " . ($user && $user->can('calling-action-browse') ? 'YES' : 'NO') . "\n";
echo "Has any permission ['institutes-browse', 'calling-action-browse']: " . ($user && $user->hasAnyPermission(['institutes-browse', 'calling-action-browse']) ? 'YES' : 'NO') . "\n";
