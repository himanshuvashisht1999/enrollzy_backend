<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$missing_modules = [
    // Academics
    'exams' => 'Exams',
    'dynamic-exams' => 'Dynamic Exams',
    'stream-offereds' => 'Stream Offered',
    'specializations' => 'Specializations',
    'organisation-types' => 'Organisation Types',
    'accreditation-approvals' => 'Accreditation Approvals',
    'sports' => 'Sports',
    'facilities' => 'Facilities',
    'exam-stages' => 'Exam Stages',

    // Career Roadmap
    'career-roadmap-categories' => 'Roadmap Categories',
    'career-roadmap-stages' => 'Roadmap Stages',
    'career-roadmap-sub-modules' => 'Roadmap Sub Modules',

    // Expert Management
    'experts' => 'Experts',
    'expert-categories' => 'Expert Categories',

    // Alumni Management
    'alumni' => 'Alumni',

    // Community
    'community-categories' => 'Community Categories',
    'community-questions' => 'Community Questions',

    // Noteworthy
    'noteworthy-categories' => 'Noteworthy Categories',
    'noteworthy-mentions' => 'Noteworthy Mentions',
    
    // Other previously missed
    'customer-fields' => 'Customer Fields',
    'settings' => 'General Settings'
];

$actions = ['browse', 'read', 'edit', 'add', 'delete'];

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// We get the admin role (for admin guard)
$adminRole = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
if (!$adminRole) {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
}

$allPermissions = Permission::where('guard_name', 'admin')->pluck('id')->toArray();

foreach ($missing_modules as $slug => $title) {
    foreach ($actions as $action) {
        $permName = "{$slug}-{$action}";
        $permission = Permission::updateOrCreate(
            ['name' => $permName, 'guard_name' => 'admin'],
            ['module_title' => $title]
        );
        $allPermissions[] = $permission->id;
    }
}

$adminRole->syncPermissions($allPermissions);

// Do the same for superadmin
$superadmin = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
if ($superadmin) {
    $superadmin->syncPermissions($allPermissions);
}

echo "Supplementary permissions seeded and assigned successfully.\n";
