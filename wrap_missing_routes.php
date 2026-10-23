<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\routes\\web.php';
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
    'admin.alumni.index' => 'alumni',

    // Community
    'admin.community-categories.index' => 'community-categories',
    'admin.community-questions.index' => 'community-questions',

    // Noteworthy
    'admin.noteworthy-categories.index' => 'noteworthy-categories',
    'admin.noteworthy-mentions.index' => 'noteworthy-mentions',

    // Missing from earlier
    'admin.customer-fields.index' => 'customer-fields',
    'admin.settings.index' => 'settings',
    
    // CMS
    'blogs.index' => 'blogs',
    'categories.index' => 'categories',
    'faqs.index' => 'faqs',
    'testimonials.index' => 'testimonials',
    'admin.video-testimonials.index' => 'video-testimonials',
    'admin.contact-us.edit' => 'contact-us',
    'admin.about_us.edit' => 'about-us'
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
