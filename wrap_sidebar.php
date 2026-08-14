<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$map = [
    'admin.hr.departments.index' => 'department',
    'admin.hr.designation.index' => 'designation',
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


foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    
    // Sub-links look like: <li> <a ... href="{{ route('xyz') }}">...</a> </li>
    // We want to match from <li> to </li> without crossing another <li>.
    $pattern = '/<li>(?:(?!<li).)*?route\(\''.$route_regex.'\'\).*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        if (strpos($match, 'sub-link') !== false || strpos($match, 'nav-link') !== false) {
            return "<!-- wrapped sublink -->\n@can('$module-browse')\n" . $match . "\n@endcan\n";
        }
        return $match;
    }, $content);
}

// Top level links: `<li class="nav-item">` to `</li>` without crossing `<ul>`
foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    $pattern = '/<li class="nav-item">(?:(?!<ul).)*?route\(\''.$route_regex.'\'\).*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        return "<!-- wrapped toplevel -->\n@can('$module-browse')\n" . $match . "\n@endcan\n";
    }, $content);
}

file_put_contents('c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master_modified.blade.php', $content);
echo "Done replacing.";
