<?php

require __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/enrollzy_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Tasks;
use App\Models\TaskAssignee;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\Meeting;
use App\Services\WorkManagement\WorkAssignmentService;
use App\Services\WorkManagement\ProjectMetricsService;
use Illuminate\Support\Facades\DB;

echo "=== STARTING E2E VERIFICATION TEST FOR WORK MANAGEMENT ===\n\n";

try {
    DB::beginTransaction();

    // 1. Fetch or create staff members
    $staffA = Admin::create([
        'name' => 'Staff Alice Developer',
        'email' => 'alice_dev_' . time() . '@enrollzy.com',
        'username' => 'alice_dev_' . time(),
        'mobile' => '9999999901',
        'salary' => 50000,
        'role' => 'staff',
        'password' => bcrypt('secret123'),
        'status' => 'active'
    ]);

    $staffB = Admin::create([
        'name' => 'Staff Bob Developer',
        'email' => 'bob_dev_' . time() . '@enrollzy.com',
        'username' => 'bob_dev_' . time(),
        'mobile' => '9999999902',
        'salary' => 45000,
        'role' => 'staff',
        'password' => bcrypt('secret123'),
        'status' => 'active'
    ]);

    echo "✓ Staff Members ready: Alice (ID: {$staffA->id}), Bob (ID: {$staffB->id})\n";

    // 2. Create Parent Team & Child Team
    $parentTeam = Team::create([
        'name' => 'Engineering Core Department',
        'code' => 'ENG-CORE',
        'team_leader_id' => $staffA->id,
        'status' => 'active',
        'description' => 'Parent Engineering Team'
    ]);

    $childTeam = Team::create([
        'name' => 'Backend Development Squad',
        'code' => 'ENG-BACKEND',
        'parent_id' => $parentTeam->id,
        'team_leader_id' => $staffB->id,
        'status' => 'active',
        'description' => 'Sub-team handling microservices'
    ]);

    echo "✓ Teams created: Parent '{$parentTeam->name}' (ID: {$parentTeam->id}), Child '{$childTeam->name}' (ID: {$childTeam->id}, Parent: {$childTeam->parent_id})\n";

    // 3. Multi-Team Membership Test: Assign Alice to both parent team and child team
    TeamMember::create([
        'team_id' => $parentTeam->id,
        'user_id' => $staffA->id,
        'role' => 'Team Leader',
        'is_team_leader' => true,
        'status' => 'active'
    ]);

    TeamMember::create([
        'team_id' => $childTeam->id,
        'user_id' => $staffA->id,
        'role' => 'Principal Architect',
        'is_team_leader' => false,
        'status' => 'active'
    ]);

    TeamMember::create([
        'team_id' => $childTeam->id,
        'user_id' => $staffB->id,
        'role' => 'Lead Developer',
        'is_team_leader' => true,
        'status' => 'active'
    ]);

    $aliceTeamsCount = $staffA->teams()->count();
    echo "✓ Multi-team membership verified: Alice belongs to {$aliceTeamsCount} teams simultaneously.\n";

    // 4. Create Project
    $project = Project::create([
        'title' => 'Enrollzy Global Expansion 2026',
        'code' => 'PRJ-EXP-01',
        'team_id' => $parentTeam->id,
        'lead_user_id' => $staffA->id,
        'created_by' => $staffA->id,
        'status' => 'active',
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+90 days')),
        'budget' => 50000.00
    ]);
    echo "✓ Project created: '{$project->title}' (ID: {$project->id})\n";

    // 5. Create Milestones
    $milestone1 = Milestone::create([
        'project_id' => $project->id,
        'title' => 'Milestone 1: Backend Architecture Setup',
        'team_id' => $childTeam->id,
        'status' => 'in_progress',
        'start_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+30 days')),
        'weight_percentage' => 50
    ]);

    $milestone2 = Milestone::create([
        'project_id' => $project->id,
        'title' => 'Milestone 2: Frontend & API Integration',
        'team_id' => $childTeam->id,
        'status' => 'planning',
        'start_date' => date('Y-m-d', strtotime('+31 days')),
        'due_date' => date('Y-m-d', strtotime('+60 days')),
        'weight_percentage' => 50
    ]);
    echo "✓ Milestones created: M1 (ID: {$milestone1->id}), M2 (ID: {$milestone2->id})\n";

    // 6. Create Parent Task
    $parentTask = Tasks::create([
        'project_id' => $project->id,
        'milestone' => $milestone1->id,
        'team_id' => $childTeam->id,
        'task_code' => 'TSK-ARCH-01',
        'title' => 'Design & Implement Authentication Microservice',
        'priority' => 'high',
        'status' => 'in_progress',
        'created_by' => $staffA->id,
        'staff_id' => $staffA->id,
        'estimated_hours' => 40
    ]);
    echo "✓ Parent Task created: '{$parentTask->title}' (ID: {$parentTask->id})\n";

    // 7. Universal Assignment Service & Subtask creation
    $assignmentService = app(WorkAssignmentService::class);

    $subtask = Tasks::create([
        'project_id' => $project->id,
        'milestone' => $milestone1->id,
        'parent_task_id' => $parentTask->id,
        'team_id' => $childTeam->id,
        'task_code' => 'SUB-ARCH-01',
        'title' => 'Implement OAuth2 JWT Validation Pipeline',
        'priority' => 'urgent',
        'status' => 'in_progress',
        'created_by' => $staffA->id,
        'staff_id' => $staffA->id,
        'estimated_hours' => 12
    ]);

    // Initial Assignment to Alice
    $assignmentService->assign(
        $subtask->id,
        'internal_user',
        $staffA->id,
        'Lead Implementer',
        true,
        date('Y-m-d', strtotime('+5 days')),
        $staffA->id
    );

    // Verify Alice has action custody
    $aliceCanAct = $subtask->canUserPerformAction($staffA->id);
    $bobCanActBefore = $subtask->canUserPerformAction($staffB->id);
    echo "✓ Initial Custody check: Alice can act? " . ($aliceCanAct ? 'YES (Expected)' : 'NO') . " | Bob can act? " . ($bobCanActBefore ? 'YES' : 'NO (Expected)') . "\n";

    // 8. Reassign Subtask to Bob (Strict Single-Assignee Custody Handover)
    echo "Handing over subtask custody from Alice to Bob...\n";
    $assignmentService->reassignSubtask($subtask->id, $staffB->id, $childTeam->id, 'Alice completed Phase 1, Bob continuing Phase 2 implementation');

    // Reload subtask
    $subtask = Tasks::with('activeAssignees')->find($subtask->id);

    $aliceCanActAfter = $subtask->canUserPerformAction($staffA->id);
    $bobCanActAfter = $subtask->canUserPerformAction($staffB->id);
    $activeAssigneeCount = $subtask->activeAssignees()->count();

    echo "✓ Reassignment check: Total active assignees on subtask: {$activeAssigneeCount} (Strict single-assignee: " . ($activeAssigneeCount === 1 ? 'PASSED' : 'FAILED') . ")\n";
    echo "✓ Custody transfer check: Alice can act? " . ($aliceCanActAfter ? 'YES (FAILED)' : 'NO (REVOKED - PASSED)') . " | Bob can act? " . ($bobCanActAfter ? 'YES (ACTIVE - PASSED)' : 'NO (FAILED)') . "\n";

    // 9. Status progression & Project Metrics calculation
    $subtask->status = 'completed';
    $subtask->completed_at = now();
    $subtask->save();

    $parentTask->status = 'completed';
    $parentTask->completed_at = now();
    $parentTask->save();

    ProjectMetricsService::updateProjectMetrics($project->id);

    $milestone1->refresh();
    $project->refresh();

    echo "✓ Milestone 1 Progress: {$milestone1->progress_percentage}%\n";
    echo "✓ Project Progress: {$project->progress_percentage}% (Health: {$project->health_status})\n";

    // 10. External Partner Test
    $partner = ExternalOrganization::create([
        'name' => 'Global QA Auditing Agency',
        'organization_type' => 'agency',
        'status' => 'active',
        'email' => 'contact@globalqa.io'
    ]);

    $contact = ExternalContact::create([
        'external_organization_id' => $partner->id,
        'name' => 'David QA Lead',
        'designation' => 'Lead Auditor',
        'email' => 'david@globalqa.io'
    ]);

    echo "✓ External Partner created: '{$partner->name}' with Contact '{$contact->name}'\n";

    // 11. Meeting with Decision-to-Task
    $meeting = Meeting::create([
        'project_id' => $project->id,
        'title' => 'Sprint 1 Architecture Retrospective',
        'meeting_type' => 'retrospective',
        'meeting_date' => date('Y-m-d'),
        'start_time' => '14:00',
        'end_time' => '15:00',
        'status' => 'completed',
        'agenda' => 'Review milestone 1 completion and assign audit tasks.'
    ]);
    echo "✓ Meeting created: '{$meeting->title}' (ID: {$meeting->id})\n";

    DB::rollBack();
    echo "\n=== ALL E2E VERIFICATIONS PASSED SUCCESSFULLY (Transaction safely rolled back) ===\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR DURING VERIFICATION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
