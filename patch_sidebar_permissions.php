<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$map = [
    // HR Module
    'admin.hr.leaves.index' => 'leaves',
    'admin.hr.leave-settings.index' => 'leaves-setting',
    'admin.hr.leave-policies.index' => 'leaves-setting', 
    'admin.hr.holidays.index' => 'holiday',
    
    'admin.hr.departments.index' => 'department',
    'admin.hr.designations.index' => 'designation',
    'admin.hr.staff-types.index' => 'staff',
    'admin.hr.staff.index' => 'staff',
    'admin.hr.roles.index' => 'roles',
    'admin.hr.banks.index' => 'staff', 
    
    'admin.hr.attendance.index' => 'attandance',
    'admin.hr.advance.index' => 'advancepay',
    'admin.hr.payroll.index' => 'payroll',
    'admin.hr.payout.index' => 'payout',
    
    'admin.hr.whatsapp_template.index' => 'whatsapp', 
    'admin.hr.whatsapp_template.report' => 'whatsapp',
    
    'admin.hr.projects.lead-sources.index' => 'project',
    'admin.hr.projects.clients.index' => 'project',
    'admin.hr.projects.project-categories.index' => 'project',
    'admin.hr.projects.index.index' => 'project',
    'admin.hr.projects.milestones.index' => 'milestone',
    'admin.hr.projects.tasks.index' => 'task',

    // Other missing ones
    'admin.courses.index' => 'courses',
    'admin.trending-skills.index' => 'trending-skills',
    'admin.program-levels.index' => 'program-levels',
    'admin.program-types.index' => 'program-types',
    'admin.disciplines.index' => 'disciplines',
    'admin.campus-types.index' => 'campus-types',
    'admin.campus_type_new.index' => 'campus-types',
    'admin.languages.index' => 'languages',
    'admin.caste-categories.index' => 'caste-categories',

    'admin.slots.index' => 'experts',
    'admin.bookings.index' => 'experts',

    'leads.index\', [\'type\' => \'Alumni\']' => 'alumni',
    
    'admin.community-replies.index' => 'community-replies',
];

// First, wrap individual <li> items
foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    $pattern = '/<li>(?:(?!<li).)*?route\(\''.$route_regex.'\'(?:(?!<\/li>).)*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        return "<!-- wrapped $module -->\n@can('".$module."-browse')\n" . $match . "\n@endcan\n";
    }, $content);
}

// Now wrap parent groups with @canany
$groups = [
    [
        'id' => '#academicsMenu',
        'permissions' => ['organisations-browse', 'organisation-courses-browse', 'exams-browse', 'dynamic-exams-browse', 'courses-browse', 'noteworthy-categories-browse', 'noteworthy-mentions-browse', 'trending-skills-browse', 'program-levels-browse', 'program-types-browse', 'stream-offereds-browse', 'disciplines-browse', 'specializations-browse', 'organisation-types-browse', 'accreditation-approvals-browse', 'campus-types-browse', 'facilities-browse', 'sports-browse', 'exam-stages-browse'],
    ],
    [
        'id' => '#careerRoadmapMenu',
        'permissions' => ['career-roadmap-categories-browse', 'career-roadmap-stages-browse', 'career-roadmap-sub-modules-browse'],
    ],
    [
        'id' => '#faqMenu',
        'permissions' => ['faqs-browse', 'categories-browse'],
    ],
    [
        'id' => '#expertMenu',
        'permissions' => ['experts-browse', 'expert-categories-browse'],
    ],
    [
        'id' => '#alumniMenu',
        'permissions' => ['alumni-browse'],
    ],
    [
        'id' => '#mentorMenu',
        'permissions' => ['mentor-profiles-browse', 'mentor-verifications-browse', 'mentor-languages-browse', 'mentor-degrees-browse', 'mentor-industries-browse', 'mentor-commissions-browse'],
    ],
    [
        'id' => '#communityMenu',
        'permissions' => ['community-categories-browse', 'community-questions-browse', 'community-replies-browse'],
    ],
    [
        'id' => '#hrMenu',
        'permissions' => ['leaves-browse', 'leaves-setting-browse', 'holiday-browse', 'department-browse', 'designation-browse', 'staff-browse', 'roles-browse', 'attandance-browse', 'advancepay-browse', 'payroll-browse', 'payout-browse', 'project-browse', 'milestone-browse', 'task-browse'],
    ],
    [
        'id' => '#contentMenu',
        'permissions' => ['blogs-browse', 'categories-browse', 'faqs-browse', 'testimonials-browse', 'video-testimonials-browse', 'contact-us-browse'],
    ],
    [
        'id' => '#homepageMenu',
        'permissions' => ['homepage-sections-browse', 'homepage-stream-tabs-browse', 'header-links-browse', 'mega-menu-browse', 'header-menus-browse', 'footer-setup-browse', 'hero-sliders-browse', 'trending-skills-browse', 'school-marquees-browse', 'institute-marquees-browse', 'home-services-browse', 'pages-browse'],
    ],
    [
        'id' => '#billingMenu',
        'permissions' => ['billing-services-browse', 'billing-invoices-browse', 'billing-payments-browse'],
    ],
    [
        'id' => '#seoMenu',
        'permissions' => ['seo-organization-browse', 'seo-homepage-browse', 'seo-defaults-browse'],
    ]
];

$parts = explode('<li class="nav-item">', $content);
foreach ($parts as $index => &$part) {
    if ($index === 0) continue;
    
    foreach ($groups as $group) {
        $id = $group['id'];
        if (strpos($part, 'href="' . $id . '"') !== false && strpos($part, 'data-bs-toggle="collapse"') !== false) {
            // Check if not already wrapped
            if (strpos($parts[$index - 1], '@canany') === false) {
                // Add @canany to the end of the previous part
                $permissionsString = "'" . implode("', '", $group['permissions']) . "'";
                $parts[$index - 1] .= "\n<!-- wrapped parent $id -->\n@canany([" . $permissionsString . "])\n";
                
                // Add @endcanany at the end of the block
                $part = preg_replace('/(<\/ul>\s*<\/div>\s*<\/li>)/s', "$1\n@endcanany\n", $part, 1);
            }
            break;
        }
    }
}
$content = implode('<li class="nav-item">', $parts);

file_put_contents($file, $content);
echo "Sidebar permissions patched successfully.\n";
