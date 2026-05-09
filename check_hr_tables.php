<?php
$tables = ['admin', 'leaves', 'leave_policies', 'leave_settings', 'declared_holidays', 'setting', 'organizations', 'department', 'designation'];
foreach($tables as $t) {
    try {
        $exists = Schema::hasTable($t);
        echo "$t: " . ($exists ? "EXISTS" : "MISSING") . "\n";
    } catch (\Exception $e) {
        echo "$t: ERROR (" . $e->getMessage() . ")\n";
    }
}
