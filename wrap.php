<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$map = [
    // Core
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
    
    // Projects
    'admin.hr.projects.index' => 'project',
    'admin.hr.tasks.index' => 'task',
    'admin.hr.milestones.index' => 'milestone',

    // System Configs
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

    // Organizations
    'admin.organisations.index' => 'organisations',
    'admin.organisation-courses.index' => 'organisation-courses',
    'admin.institutes.index' => 'institutes',

    // Students
    'admin.customers.main.index.index' => 'customer',
    'admin.customer-categories.index' => 'customer-category',
    'admin.customer-fields.index' => 'customer-fields',
    'admin.interested-ins.index' => 'interested-ins',
    'admin.customer-sessions.index' => 'customer-sessions',

    // Experts
    'admin.consultants.index' => 'consultant',
    'admin.consultant-categories.index' => 'consultant-category',
    'admin.consultant-settings.index' => 'consultant-setting',

    // Calling
    'admin.students-crm.calling-statuses.index' => 'calling-status',
    'admin.students-crm.calling-actions.index' => 'calling-action',
    'admin.students-crm.calling-module.index' => 'calling-module',
    'admin.students-crm.calling-history.index' => 'calling-history',

    // CMS
    'blogs.index' => 'blogs',
    'categories.index' => 'categories',
    'faqs.index' => 'faqs',
    'testimonials.index' => 'testimonials',
    'admin.video-testimonials.index' => 'video-testimonials',
    'admin.contact-us.edit' => 'contact-us',
    'admin.about_us.edit' => 'about-us',

    // Homepage
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
    
    // Billing
    'admin.billing.services.index' => 'billing-services',
    'admin.billing.invoices.index' => 'billing-invoices',
    'admin.billing.payments.index' => 'billing-payments',
    
    // SEO
    'admin.seo_organization.edit' => 'seo-organization',
    'admin.seo_homepage.edit' => 'seo-homepage',
    'admin.seo_defaults.edit' => 'seo-defaults',
    
    // Others
    'admin.scholarships.index' => 'scholarships',
    'leads.index' => 'lead',
    'admin.settings.index' => 'settings',
    'admin.commission.index' => 'commission',
];

// Instead of regex on li class=nav-item, I'll match each route in the file and wrap the <li> block.
// Let's use preg_replace. Each block is like:
// <li class="nav-item"> ... route('xyz') ... </li>

foreach ($map as $route => $module) {
    // Find the <li class="nav-item"> block that contains this route exactly
    $pattern = '/<li class="nav-item">(?:.(?!<\/li>))*?route\(\''.$route.'\'\)(?:.(?!<\/li>))*?<\/li>/s';
    
    // However, some routes are like route('admin.customers.main.index.index', ['type' => 'class']).
    // Let's use a simpler pattern: find <li class="nav-item"> ... $route ... </li>
    // We can do this safely if we just wrap the <li>. But wait, what about sub-menus? They are <li> inside <ul> inside <li>.
    // Let's wrap the specific <a class="nav-link ..."> or <li ...> for simple items.
}

// Actually, since there are many ways menus are formed (collapsible ones have <li> inside <ul>),
// let's do manual str_replace for the most prominent collapsible blocks first.
