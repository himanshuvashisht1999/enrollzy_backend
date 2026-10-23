<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

// Replace hasAnyPermission
$content = preg_replace(
    '/@if\(\$user && \$user->hasAnyPermission\(\[(.*?)\]\)\)/s', 
    '@if($user && method_exists($user, \'hasAnyPermission\') && $user->hasAnyPermission([$1]))', 
    $content
);

// Replace can (for safety, though all authenticatables usually have it)
$content = preg_replace(
    '/@if\(\$user && \$user->can\((.*?)\)\)/s', 
    '@if($user && method_exists($user, \'can\') && $user->can($1))', 
    $content
);

file_put_contents($file, $content);
echo "Fixed method_exists in master.blade.php\n";
