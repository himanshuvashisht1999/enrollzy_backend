<?php
$c = file_get_contents('resources/views/admin/layouts/master.blade.php'); 
echo substr($c, strpos($c, '#academicsMenu') - 200, 400);
