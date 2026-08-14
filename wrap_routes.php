<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\routes\\web.php';
$content = file_get_contents($file);

$map = [
    'admin.hr.departments.index' => 'department',
    'admin.hr.designations.index' => 'designation',
    'admin.hr.staff.index' => 'staff',
    'admin.hr.roles.index' => 'roles',
    'admin.hr.holidays.index' => 'holiday',
    'admin.hr.attendance.index' => 'attandance',
    'admin.hr.advancepay.index' => 'advancepay',
    'admin.hr.payroll.index' => 'payroll',
    'admin.hr.payout.index' => 'payout',
    'admin.hr.leaves.index' => 'leaves',
    'admin.hr.leave_settings.index' => 'leaves-setting',
    
    'admin.hr.projects.index' => 'project',
    'admin.hr.tasks.index' => 'task',
    'admin.hr.milestones.index' => 'milestone',

    'admin.program-levels.index' => 'program-levels',
    'admin.program-types.index' => 'program-types',
    'admin.streams.index' => 'streams',
    'admin.disciplines.index' => 'disciplines',
    'admin.courses.index' => 'courses',
    'admin.trending-courses.index' => 'trending-courses',
    'admin.campus-types.index' => 'campus-types',
    'admin.amenities.index' => 'amenities',
    'admin.country.index' => 'country',
    'admin.states.index' => 'states',
    'admin.cities.index' => 'cities',
    'admin.user_types.index' => 'user-types',
    'admin.permissions.index' => 'system-permissions',

    'admin.organisations.index' => 'organisations',
    'admin.organisation-courses.index' => 'organisation-courses',
    'admin.institutes.index' => 'institutes',

    'admin.customers.main.index.index' => 'customer',
    'admin.customer-categories.index' => 'customer-category',
    'admin.customer-fields.index' => 'customer-fields',
    'admin.interested-ins.index' => 'interested-ins',
    'admin.customer-sessions.index' => 'customer-sessions',

    'admin.consultants.index' => 'consultant',
    'admin.consultant-categories.index' => 'consultant-category',
    'admin.consultant-settings.index' => 'consultant-setting',

    'admin.students-crm.calling-statuses.index' => 'calling-status',
    'admin.students-crm.calling-actions.index' => 'calling-action',
    'admin.students-crm.calling-module.index' => 'calling-module',
    'admin.students-crm.calling-history.index' => 'calling-history',

    'blogs.index' => 'blogs',
    'categories.index' => 'categories',
    'faqs.index' => 'faqs',
    'testimonials.index' => 'testimonials',
    'admin.video-testimonials.index' => 'video-testimonials',
    'admin.contact-us.edit' => 'contact-us',
    'admin.about_us.edit' => 'about-us',

    'homepage-sections.index' => 'homepage-sections',
    'admin.homepage-stream-tabs.index' => 'homepage-stream-tabs',
    'admin.header-links.index' => 'header-links',
    'admin.mega-menu.index' => 'mega-menu',
    'admin.header-menus.index' => 'header-menus',
    'admin.footer-setup.index' => 'footer-setup',
    'admin.hero-sliders.index' => 'hero-sliders',
    'admin.trending-skills.index' => 'trending-skills',
    'admin.school-marquees.index' => 'school-marquees',
    'admin.institute-marquees.index' => 'institute-marquees',
    'admin.home-services.index' => 'home-services',
    'admin.pages.index' => 'pages',
    
    'admin.billing.services.index' => 'billing-services',
    'admin.billing.invoices.index' => 'billing-invoices',
    'admin.billing.payments.index' => 'billing-payments',
    
    'admin.seo_organization.edit' => 'seo-organization',
    'admin.seo_homepage.edit' => 'seo-homepage',
    'admin.seo_defaults.edit' => 'seo-defaults',
    
    'admin.scholarships.index' => 'scholarships',
    'leads.index' => 'lead',
    'admin.settings.index' => 'settings',
    'admin.commission.index' => 'commission',
];

$lines = explode("\n", $content);
$new_lines = [];
foreach ($lines as $line) {
    if (strpos($line, '->middleware(') !== false && strpos($line, 'can:') !== false) {
        $new_lines[] = $line;
        continue;
    }

    $matched = false;
    foreach ($map as $route => $module) {
        if (strpos($line, "->name('$route')") !== false || strpos($line, "->name(\"$route\")") !== false) {
            $line = preg_replace('/;$/', "->middleware('can:$module-browse');", rtrim($line));
            $matched = true;
            break;
        }
    }
    
    if (!$matched && preg_match('/Route::resource\(([\'"])([^\'"]+)\1/', $line, $res_matches)) {
        $uri = $res_matches[2];
        foreach ($map as $route => $module) {
            $prefix = explode('.', $route)[0];
            if (strpos($uri, ltrim($prefix, '/')) !== false || strpos($route, str_replace('/admin/', '', $uri)) !== false) {
                if (strpos($line, ';') !== false) {
                    $line = preg_replace('/;$/', "->middleware('can:$module-browse');", rtrim($line));
                    $matched = true;
                    break;
                }
            }
        }
    }
    
    $new_lines[] = rtrim($line);
}

file_put_contents('c:\\xampp\\htdocs\\enrollzy_backend\\routes\\web.php', implode("\n", $new_lines));
echo "Done replacing web.php";
