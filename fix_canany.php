<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

// Replace hasAnyPermission with canAny
$content = str_replace('hasAnyPermission', 'canAny', $content);

file_put_contents($file, $content);
echo "Replaced hasAnyPermission with canAny in master.blade.php\n";
