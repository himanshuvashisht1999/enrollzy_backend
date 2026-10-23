<?php
$c = file_get_contents('resources/views/admin/layouts/master.blade.php'); 
$pos = strpos($c, '@can(\'institutes-browse\')'); 
echo substr($c, $pos - 100, 300);
