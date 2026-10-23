<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

$map = [
    'admin.customers.main.index.index' => 'customer',
    'admin.customer-categories.index' => 'customer-category',
    'admin.customer-fields.index' => 'customer-fields',
    'admin.institutes.index' => 'institutes',
    'admin.interested-ins.index' => 'interested-ins',
    'admin.customer-sessions.index' => 'customer-sessions',
    
    'admin.consultants.index' => 'consultant',
    'admin.consultant-categories.index' => 'consultant-category',
    'admin.consultant-settings.index' => 'consultant-setting',
    
    'admin.students-crm.calling-statuses.index' => 'calling-status',
    'admin.students-crm.calling-actions.index' => 'calling-action',
    'admin.students-crm.calling-module.index' => 'calling-module',
    'admin.students-crm.calling-history.index' => 'calling-history',

    'admin.about_us.edit' => 'about-us',
    
    'admin.seo_homepage.edit' => 'seo-homepage',
    'admin.seo_defaults.edit' => 'seo-defaults',
    'admin.commission.index' => 'commission',
    
    'admin.billing.services.index' => 'billing-services',
    'admin.billing.invoices.index' => 'billing-invoices',
    'admin.billing.payments.index' => 'billing-payments',
    
    'admin.seo_organization.edit' => 'seo-organization',
    'admin.scholarships.index' => 'scholarships',
    'admin.settings.index' => 'settings',
];

foreach ($map as $route => $module) {
    $route_regex = preg_quote($route, '/');
    $pattern = '/<li class="nav-item">(?:(?!<li).)*?route\(\''.$route_regex.'\'.*?<\/li>/s';
    
    $content = preg_replace_callback($pattern, function($matches) use ($module) {
        $match = $matches[0];
        if (strpos($match, '@can') !== false) { return $match; }
        return "<!-- wrapped standalone $module -->\n@can('".$module."-browse')\n" . $match . "\n@endcan";
    }, $content);
}

// Special case for 'admin.customers.main.index.index', ['type' => 'class']
$route_regex = preg_quote("admin.customers.main.index.index', ['type' => 'class']", '/');
$pattern = '/<li class="nav-item">(?:(?!<li).)*?route\(\''.$route_regex.'\'.*?<\/li>/s';
$content = preg_replace_callback($pattern, function($matches) {
    $match = $matches[0];
    if (strpos($match, '@can') !== false) { return $match; }
    return "<!-- wrapped standalone classes -->\n@can('customer-browse')\n" . $match . "\n@endcan";
}, $content);

file_put_contents($file, $content);
echo "Standalone sidebar permissions patched successfully.\n";
