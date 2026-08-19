<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Admin::where('role', 'superadmin')->first();
\Auth::guard('admin')->login($user);

echo "canAny: " . ($user && method_exists($user, 'canAny') && $user->canAny(['leaves-browse', 'holiday-browse']) ? 'YES' : 'NO') . "\n";
echo "hasAnyPermission: " . ($user && method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission(['leaves-browse', 'holiday-browse']) ? 'YES' : 'NO') . "\n";
echo "is superadmin? " . ($user->role === 'superadmin' ? 'YES' : 'NO') . "\n";
