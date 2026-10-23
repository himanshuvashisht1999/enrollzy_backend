<?php
$content = file_get_contents('c:\xampp\htdocs\enrollzy_backend\resources\views\admin\students_crm\calling\dashboard.blade.php');
$start = strpos($content, '<!-- Update Calling Status Modal -->');
$end = strpos($content, '<script src=', $start);
if ($start !== false && $end !== false) {
    file_put_contents('c:\xampp\htdocs\enrollzy_backend\callModal.txt', substr($content, $start, $end - $start));
    echo "Extracted successfully\n";
} else {
    echo "Could not extract. Start: " . ($start !== false) . ", End: " . ($end !== false) . "\n";
}
