<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$map = [
    // Academics
    'admin.exams.index' => 'exams',
    'admin.dynamic-exams.index' => 'dynamic-exams',
    'admin.stream-offereds.index' => 'stream-offereds',
    'admin.specializations.index' => 'specializations',
    'admin.organisation-types.index' => 'organisation-types',
    'admin.accreditation-approvals.index' => 'accreditation-approvals',
    'admin.sports.index' => 'sports',
    'admin.facilities.index' => 'facilities',
    'admin.exam-stages.index' => 'exam-stages',

    // Career Roadmap
    'admin.career-roadmap-categories.index' => 'career-roadmap-categories',
    'admin.career-roadmap-stages.index' => 'career-roadmap-stages',
    'admin.career-roadmap-sub-modules.index' => 'career-roadmap-sub-modules',

    // Expert Management
    'experts.index' => 'experts',
    'expert-categories.index' => 'expert-categories',

    // Alumni Management
    'admin.alumni.index' => 'alumni', // check actual route below

    // Community
    'admin.community-categories.index' => 'community-categories',
    'admin.community-questions.index' => 'community-questions',

    // Noteworthy
    'admin.noteworthy-categories.index' => 'noteworthy-categories',
    'admin.noteworthy-mentions.index' => 'noteworthy-mentions',

    // Missing from earlier
    'admin.customer-fields.index' => 'customer-fields',
    'admin.settings.index' => 'settings'
];

foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    
    // Sub-links
    $pattern = '/<li>(?:(?!<li).)*?route\(\''.$route_regex.'\'\).*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        if (strpos($match, 'sub-link') !== false || strpos($match, 'nav-link') !== false) {
            return "<!-- wrapped sublink missing -->\n@can('$module-browse')\n" . $match . "\n@endcan\n";
        }
        return $match;
    }, $content);
}

// Top level links
foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    $pattern = '/<li class="nav-item">(?:(?!<ul).)*?route\(\''.$route_regex.'\'\).*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        return "<!-- wrapped toplevel missing -->\n@can('$module-browse')\n" . $match . "\n@endcan\n";
    }, $content);
}

// Let's also make sure CMS links were wrapped, as they were missed earlier because they use a slightly different href syntax or are missing 'index'.
$cms_map = [
    'blogs.index' => 'blogs',
    'categories.index' => 'categories',
    'faqs.index' => 'faqs',
    'testimonials.index' => 'testimonials',
    'admin.video-testimonials.index' => 'video-testimonials',
    'admin.contact-us.edit' => 'contact-us',
    'admin.about_us.edit' => 'about-us'
];

foreach ($cms_map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    $pattern = '/<li>(?:(?!<li).)*?route\(\''.$route_regex.'\'\).*?<\/li>/s';
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        return "<!-- wrapped cms missing -->\n@can('$module-browse')\n" . $match . "\n@endcan\n";
    }, $content);
}

file_put_contents('c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master_modified.blade.php', $content);
echo "Done replacing missing items.";
