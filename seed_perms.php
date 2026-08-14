<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

$modules = [
    // Core Management
    'department' => 'Departments',
    'designation' => 'Designations',
    'staff' => 'Staff',
    'roles' => 'Roles & Permissions',
    'leaves-setting' => 'Leave Settings',
    'leaves' => 'Leaves',
    'holiday' => 'Holidays',
    'attandance' => 'Attendance', // Keeping typo to match existing
    'advancepay' => 'Advance Pay',
    'payroll' => 'Payroll',
    'payout' => 'Payout',
    
    // Projects / Tasks
    'project' => 'Projects',
    'task' => 'Tasks',
    'milestone' => 'Milestones',

    // System Configurations
    'program-levels' => 'Program Levels',
    'program-types' => 'Program Types',
    'streams' => 'Streams',
    'disciplines' => 'Disciplines',
    'courses' => 'Courses',
    'trending-courses' => 'Trending Courses',
    'campus-types' => 'Campus Types',
    'amenities' => 'Amenities',
    'country' => 'Countries',
    'states' => 'States',
    'cities' => 'Cities',
    'user-types' => 'User Types',
    'system-permissions' => 'System Permissions',
    
    // Organizations
    'organisations' => 'Organisations',
    'organisation-courses' => 'Organisation Courses',
    'institutes' => 'Institutes',

    // Customers / Students
    'customer' => 'Students',
    'customer-category' => 'Student Categories',
    'customer-fields' => 'Customer Fields',
    'interested-ins' => 'Interested Ins',
    'customer-sessions' => 'Sessions',
    
    // Experts / Consultants
    'consultant' => 'Consultants',
    'consultant-category' => 'Consultant Categories',
    'consultant-setting' => 'Consultant Settings',
    
    // CRM
    'calling-status' => 'Calling Statuses',
    'calling-action' => 'Calling Actions',
    'calling-module' => 'Calling Module',
    'calling-history' => 'Calling History',
    'lead' => 'Leads',
    'commission' => 'Commissions',
    
    // CMS
    'blogs' => 'Blogs',
    'categories' => 'Blog Categories',
    'faqs' => 'FAQs',
    'testimonials' => 'Testimonials',
    'video-testimonials' => 'Video Stories',
    'contact-us' => 'Contact Us',
    'about-us' => 'About Us',
    
    // Homepage
    'homepage-sections' => 'Manage Sections',
    'homepage-stream-tabs' => 'Stream Tabs',
    'header-links' => 'Header Links',
    'mega-menu' => 'Mega Dropdown Menu',
    'header-menus' => 'Main Header Menus',
    'footer-setup' => 'Footer Setup',
    'hero-sliders' => 'Hero Sliders',
    'trending-skills' => 'Trending Skills',
    'school-marquees' => 'School Marquee',
    'institute-marquees' => 'Institute Marquee',
    'home-services' => 'Why Choose Us',
    'home-benefits' => 'Benefits',
    'pages' => 'Dynamic Pages',

    // Billing
    'billing-services' => 'Services',
    'billing-invoices' => 'Invoices',
    'billing-payments' => 'Payments',

    // SEO
    'seo-organization' => 'Global Organization SEO',
    'seo-homepage' => 'Homepage SEO',
    'seo-defaults' => 'Global Defaults SEO',
    
    // Other
    'scholarships' => 'Scholarships',
    'settings' => 'Settings',
];

$actions = ['browse', 'read', 'edit', 'add', 'delete'];

// Clean cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

$allPermissions = [];
foreach ($modules as $slug => $title) {
    foreach ($actions as $action) {
        $permName = "{$slug}-{$action}";
        $permission = Permission::updateOrCreate(
            ['name' => $permName, 'guard_name' => 'web'],
            ['module_title' => $title]
        );
        $allPermissions[] = $permission->id;
    }
}

// Assign all permissions to admin
$adminRole->syncPermissions($allPermissions);

// Update existing permissions that might not be in the list, just in case
Permission::whereNull('module_title')->update(['module_title' => DB::raw("REPLACE(SUBSTRING_INDEX(name, '-', 1), '-', ' ')")]);

echo "Permissions seeded and assigned successfully.\n";
