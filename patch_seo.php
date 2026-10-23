<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

// Wrap Homepage SEO
$content = preg_replace(
    '/(<li><a class="nav-link sub-link[^>]*href="[^"]*admin\.seo_homepage\.edit"[^>]*>Homepage SEO<\/a><\/li>)/s', 
    "@if(\$user && method_exists(\$user, 'can') && \$user->can('seo-homepage-browse'))\n$1\n@endif", 
    $content
);

// Wrap Global Defaults
$content = preg_replace(
    '/(<li><a class="nav-link sub-link[^>]*href="[^"]*admin\.seo_defaults\.edit"[^>]*>Global Defaults<\/a><\/li>)/s', 
    "@if(\$user && method_exists(\$user, 'can') && \$user->can('seo-defaults-browse'))\n$1\n@endif", 
    $content
);

// Wrap SEO Settings Dropdown (the whole thing)
$content = preg_replace(
    '/(<!-- SEO Settings Dropdown -->.*?<\/div>)/s',
    "@if(\$user && method_exists(\$user, 'hasAnyPermission') && \$user->hasAnyPermission(['seo-organization-browse', 'seo-homepage-browse', 'seo-defaults-browse']))\n$1\n@endif",
    $content
);

file_put_contents($file, $content);
echo "Patched SEO menu.\n";
