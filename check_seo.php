<?php
$c = file_get_contents('resources/views/admin/layouts/master.blade.php'); 
$pos = strpos($c, '#seoSettingsMenu'); 
echo substr($c, $pos - 300, 2000);
