<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\layouts\\master.blade.php';
$content = file_get_contents($file);

// Replace @canany([...])
$content = preg_replace('/@canany\(\[(.*?)\]\)/s', '@if($user && $user->hasAnyPermission([$1]))', $content);
$content = preg_replace('/@endcanany/s', '@endif', $content);

// Replace @can('...')
$content = preg_replace('/@can\(\'(.*?)\'\)/s', '@if($user && $user->can(\'$1\'))', $content);
$content = preg_replace('/@endcan/s', '@endif', $content);

file_put_contents($file, $content);
echo "Replaced @can with \$user->can()\n";
