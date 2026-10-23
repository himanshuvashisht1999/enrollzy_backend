<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Admin::where('role', 'Counselor')->first();
if (!$user) {
    echo "No Counselor user found.\n";
    exit;
}

echo "User: " . $user->name . "\n";
echo "Role: " . $user->role . "\n";
echo "Spatie Roles: " . json_encode($user->roles->pluck('name')) . "\n";
echo "Spatie Permissions: " . json_encode($user->permissions->pluck('name')) . "\n";

// Authenticate as this user
\Auth::guard('admin')->login($user);

echo "Gate allows institutes-browse? " . (\Gate::allows('institutes-browse') ? 'YES' : 'NO') . "\n";
echo "Gate allows customer-fields-browse? " . (\Gate::allows('customer-fields-browse') ? 'YES' : 'NO') . "\n";
