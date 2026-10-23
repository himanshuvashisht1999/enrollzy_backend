<?php
$c = file_get_contents('resources/views/admin/layouts/master.blade.php'); 
preg_match_all('/<li class="nav-item">(?:(?!<li).)*?<\/li>/s', $c, $matches);
$unwrapped = [];
foreach ($matches[0] as $match) {
    if (strpos($match, 'collapse') !== false) continue; // It's a parent
    if (strpos($match, 'admin.dashboard') !== false) continue; // Dashboard
    if (strpos($match, 'expert.dashboard') !== false) continue;
    if (strpos($match, 'alumni.dashboard') !== false) continue;
    
    // Check if it's inside an @can. We can't do this easily with regex, but we can check if the match itself has @can?
    // Actually, my scripts put @can OUTSIDE the <li>.
    // So the <li> itself doesn't contain @can.
    // We can search for the route in the file, and see what's before it.
}
