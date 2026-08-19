<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$headings = [
    'Customers' => ['customer-browse', 'customer-category-browse', 'customer-fields-browse', 'institutes-browse', 'interested-ins-browse', 'customer-sessions-browse'],
    'Consultants' => ['consultant-browse', 'consultant-category-browse', 'consultant-setting-browse'],
    'Students' => ['customer-browse', 'calling-status-browse', 'calling-action-browse', 'calling-module-browse', 'calling-history-browse'],
    'Marketing & Content' => ['blogs-browse', 'categories-browse', 'faqs-browse', 'testimonials-browse', 'video-testimonials-browse', 'contact-us-browse', 'about-us-browse'],
    'Billing' => ['billing-services-browse', 'billing-invoices-browse', 'billing-payments-browse', 'seo-organization-browse', 'seo-homepage-browse', 'seo-defaults-browse', 'scholarships-browse', 'settings-browse', 'commission-browse']
];

foreach ($headings as $text => $perms) {
    // Escape for regex
    $text_regex = preg_quote($text, '/');
    $pattern = '/<div class="sidebar-heading[^>]*>'.$text_regex.'<\/div>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($perms) {
        $match = $matches[0];
        // If already wrapped, skip
        if (strpos($match, '@canany') !== false) { return $match; }
        
        $perms_str = "'" . implode("', '", $perms) . "'";
        return "<!-- wrapped heading -->\n@canany([".$perms_str."])\n" . $match . "\n@endcanany";
    }, $content);
}

file_put_contents($file, $content);
echo "Headings patched successfully.\n";
