<?php
require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'teams',
    'team_members',
    'external_organizations',
    'external_contacts',
    'external_teams',
    'external_team_members',
    'project_members',
    'project_external_members',
    'project_documents',
    'task_assignees',
    'task_delegations',
    'task_dependencies',
    'task_checklists',
    'task_time_entries',
    'task_recurrences',
    'task_activity_logs',
    'task_attachments',
    'task_comments',
    'meetings',
    'meeting_participants',
];

$output = [];
foreach ($tables as $t) {
    try {
        $res = DB::select("SHOW CREATE TABLE `{$t}`");
        $createTableProp = "Create Table";
        $sql = $res[0]->$createTableProp ?? $res[0]->{'Create Table'};
        $output[$t] = $sql;
    } catch (\Exception $e) {
        $output[$t] = "-- Table {$t} error: " . $e->getMessage();
    }
}

file_put_contents(__DIR__ . '/table_creates.json', json_encode($output, JSON_PRETTY_PRINT));
echo "Dumped " . count($output) . " tables.\n";
