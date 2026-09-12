<?php

namespace App\Services\WorkManagement;

use App\Models\Admin;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\Milestone;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Builder;

class WorkHierarchyService
{
    /**
     * Check if user is SuperAdmin or Admin
     */
    public static function isSuperAdmin($user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        $role = strtolower(trim($user->role ?? ''));
        return in_array($role, ['superadmin', 'admin', 'administrator', 'super admin', 'super-admin'])
            || ($user->is_admin ?? false)
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && ($user->hasRole('superadmin') || $user->hasRole('admin')));
    }

    /**
     * Check if user is a Team Leader / Manager
     */
    public static function isTeamLeader($user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        if (self::isSuperAdmin($user)) {
            return false;
        }

        // Check if leading any teams directly
        $leadsTeams = Team::where('team_leader_id', $user->id)->exists();
        if ($leadsTeams) return true;

        // Check team_members pivot
        $leaderInPivot = TeamMember::where('user_id', $user->id)->where('is_team_leader', 1)->exists();
        if ($leaderInPivot) return true;

        // Check manager_id in admin table (has direct reports)
        $hasReports = Admin::where('manager_id', $user->id)->exists();
        if ($hasReports) return true;

        // Check role title containing manager / lead / head / supervisor
        $role = strtolower(trim($user->role ?? ''));
        if (str_contains($role, 'manager') || str_contains($role, 'leader') || str_contains($role, 'lead') || str_contains($role, 'head') || str_contains($role, 'supervisor')) {
            return true;
        }

        return false;
    }

    /**
     * Get all subordinate / team member user IDs for a user (Recursive)
     */
    public static function getSubordinateUserIds($user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) return [];

        $allSubordinateIds = [(int)$user->id];
        $toProcess = [(int)$user->id];
        $processed = [];

        while (!empty($toProcess)) {
            $currentId = array_shift($toProcess);
            if (in_array($currentId, $processed)) {
                continue;
            }
            $processed[] = $currentId;

            // 1. Direct reports via manager_id in admin table
            $directReports = Admin::where('manager_id', $currentId)->pluck('id')->toArray();

            // 2. Members of teams led by this user
            $ledTeamIds = Team::where('team_leader_id', $currentId)->pluck('id')->toArray();
            $pivotTeamIds = TeamMember::where('user_id', $currentId)->where('is_team_leader', 1)->pluck('team_id')->toArray();
            $allLedTeamIds = array_unique(array_merge($ledTeamIds, $pivotTeamIds));

            $teamMemberIds = [];
            if (!empty($allLedTeamIds)) {
                $teamMemberIds = TeamMember::whereIn('team_id', $allLedTeamIds)->pluck('user_id')->toArray();
            }

            $newFound = array_unique(array_merge($directReports, $teamMemberIds));
            foreach ($newFound as $foundId) {
                $foundId = (int)$foundId;
                if (!in_array($foundId, $allSubordinateIds)) {
                    $allSubordinateIds[] = $foundId;
                    $toProcess[] = $foundId;
                }
            }
        }

        return array_values(array_unique(array_filter($allSubordinateIds)));
    }

    /**
     * Get all team IDs led by the user
     */
    public static function getLedTeamIds($user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) return [];

        $teamIds = Team::where('team_leader_id', $user->id)->pluck('id')->toArray();
        $pivotTeamIds = TeamMember::where('user_id', $user->id)->where('is_team_leader', 1)->pluck('team_id')->toArray();

        return array_values(array_unique(array_merge($teamIds, $pivotTeamIds)));
    }

    /**
     * Get all team IDs where user is either leader or member
     */
    public static function getUserTeamIds($user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) return [];

        $directTeams = Team::where('team_leader_id', $user->id)->pluck('id')->toArray();
        $memberTeams = TeamMember::where('user_id', $user->id)->pluck('team_id')->toArray();

        return array_values(array_unique(array_merge($directTeams, $memberTeams)));
    }

    /**
     * Apply Hierarchy Scope to Projects Query
     */
    public static function applyProjectScope($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return $query;

        // Organization isolation
        if ($user->organization_id) {
            $query->where('projects.organization_id', $user->organization_id);
        }

        // Tier 1: Super Admin / Admin -> Full Visibility
        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;
        $subordinateIds = self::getSubordinateUserIds($user);
        $ledTeamIds = self::getLedTeamIds($user);
        $userTeamIds = self::getUserTeamIds($user);

        return $query->where(function ($q) use ($userId, $subordinateIds, $ledTeamIds, $userTeamIds) {
            // Owned or created by leader or subordinates
            $q->whereIn('projects.owner_id', $subordinateIds)
              ->orWhereIn('projects.staff_id', $subordinateIds)
              ->orWhereIn('projects.created_by', $subordinateIds)
              // Member of project
              ->orWhereHas('members', function ($mq) use ($subordinateIds) {
                  $mq->whereIn('admin.id', $subordinateIds);
              })
              // Has tasks belonging to, assigned by, or created by subordinates
              ->orWhereHas('tasks', function ($tq) use ($subordinateIds, $ledTeamIds, $userTeamIds) {
                  $tq->whereIn('tasks.assigned_to', $subordinateIds)
                     ->orWhereIn('tasks.created_by', $subordinateIds)
                     ->orWhereIn('tasks.staff_id', $subordinateIds)
                     ->orWhereHas('activeAssignees', function ($aq) use ($subordinateIds, $userTeamIds) {
                         $aq->whereIn('task_assignees.user_id', $subordinateIds)
                            ->orWhereIn('task_assignees.team_id', $userTeamIds)
                            ->orWhereIn('task_assignees.assigned_by', $subordinateIds);
                     });

                  if (!empty($ledTeamIds)) {
                      $tq->orWhereIn('tasks.team_id', $ledTeamIds);
                  }
              });

            if (!empty($ledTeamIds)) {
                $q->orWhereIn('projects.team_id', $ledTeamIds);
            }
        });
    }

    /**
     * Apply Hierarchy Scope to Tasks Query
     */
    public static function applyTaskScope($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return $query;

        // Organization isolation
        if ($user->organization_id) {
            $query->where('tasks.organization_id', $user->organization_id);
        }

        // Tier 1: Super Admin / Admin -> Full Visibility across organization
        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;
        $subordinateIds = self::getSubordinateUserIds($user);
        $ledTeamIds = self::getLedTeamIds($user);
        $userTeamIds = self::getUserTeamIds($user);

        return $query->where(function ($q) use ($userId, $subordinateIds, $ledTeamIds, $userTeamIds, $user) {
            // 1. Assigned to or created by user or any subordinate in the reporting line
            $q->whereIn('tasks.assigned_to', $subordinateIds)
              ->orWhereIn('tasks.created_by', $subordinateIds)
              ->orWhereIn('tasks.staff_id', $subordinateIds)
              // 2. Active assignees assigned to user/subordinates or assigned BY user/subordinates
              ->orWhereHas('activeAssignees', function ($aq) use ($subordinateIds, $userTeamIds) {
                  $aq->whereIn('task_assignees.user_id', $subordinateIds)
                     ->orWhereIn('task_assignees.team_id', $userTeamIds)
                     ->orWhereIn('task_assignees.assigned_by', $subordinateIds);
              })
              // 3. Historical assignees where user/subordinate assigned or held custody
              ->orWhereHas('assignees', function ($asq) use ($subordinateIds) {
                  $asq->whereIn('task_assignees.assigned_by', $subordinateIds)
                      ->orWhereIn('task_assignees.user_id', $subordinateIds);
              })
              // 4. Delegations initiated by or sent to user/subordinates
              ->orWhereHas('delegations', function ($dq) use ($subordinateIds) {
                  $dq->whereIn('task_delegations.from_user_id', $subordinateIds)
                     ->orWhereIn('task_delegations.to_user_id', $subordinateIds);
              })
              // 5. If user owns or has access to the project
              ->orWhereHas('project', function ($pq) use ($user) {
                  self::applyProjectScope($pq, $user);
              })
              // 6. Child subtasks of a parent task visible to this user
              ->orWhereHas('parentTask', function ($ptq) use ($subordinateIds) {
                  $ptq->whereIn('tasks.assigned_to', $subordinateIds)
                      ->orWhereIn('tasks.created_by', $subordinateIds)
                      ->orWhereIn('tasks.staff_id', $subordinateIds)
                      ->orWhereHas('activeAssignees', function ($paq) use ($subordinateIds) {
                          $paq->whereIn('task_assignees.user_id', $subordinateIds);
                      });
              });

            if (!empty($ledTeamIds)) {
                $q->orWhereIn('tasks.team_id', $ledTeamIds);
            }
        });
    }

    /**
     * Apply Hierarchy Scope to Milestones Query
     */
    public static function applyMilestoneScope($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return $query;

        if ($user->organization_id) {
            $query->where('milestones.organization_id', $user->organization_id);
        }

        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;
        $subordinateIds = self::getSubordinateUserIds($user);
        $ledTeamIds = self::getLedTeamIds($user);

        return $query->where(function ($q) use ($userId, $subordinateIds, $ledTeamIds, $user) {
            $q->whereIn('milestones.owner_id', $subordinateIds)
              ->orWhereIn('milestones.lead_user_id', $subordinateIds)
              ->orWhereIn('milestones.created_by', $subordinateIds)
              ->orWhereHas('project', function ($pq) use ($user) {
                  self::applyProjectScope($pq, $user);
              })
              ->orWhereHas('tasks', function ($tq) use ($user) {
                  self::applyTaskScope($tq, $user);
              });

            if (!empty($ledTeamIds)) {
                $q->orWhereIn('milestones.team_id', $ledTeamIds);
            }
        });
    }

    /**
     * Apply Hierarchy Scope to Teams Query
     */
    public static function applyTeamScope($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return $query;

        if ($user->organization_id) {
            $query->where('teams.organization_id', $user->organization_id);
        }

        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;
        $subordinateIds = self::getSubordinateUserIds($user);
        $userTeamIds = self::getUserTeamIds($user);
        $ledTeamIds = self::getLedTeamIds($user);

        return $query->where(function ($q) use ($userId, $subordinateIds, $userTeamIds, $ledTeamIds) {
            $q->whereIn('teams.team_leader_id', $subordinateIds)
              ->orWhereIn('teams.id', $userTeamIds)
              ->orWhereIn('teams.id', $ledTeamIds)
              ->orWhereHas('members', function ($mq) use ($subordinateIds) {
                  $mq->whereIn('admin.id', $subordinateIds);
              });
        });
    }

    /**
     * Apply Hierarchy Scope to Meetings Query
     */
    public static function applyMeetingScope($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return $query;

        if ($user->organization_id) {
            $query->where('meetings.organization_id', $user->organization_id);
        }

        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;
        $subordinateIds = self::getSubordinateUserIds($user);

        return $query->where(function ($q) use ($userId, $subordinateIds, $user) {
            $q->whereIn('meetings.created_by', $subordinateIds)
              ->orWhereHas('participants', function ($pq) use ($subordinateIds) {
                  $pq->whereIn('meeting_participants.user_id', $subordinateIds);
              })
              ->orWhereHas('project', function ($projQ) use ($user) {
                  self::applyProjectScope($projQ, $user);
              });
        });
    }

    /**
     * Get Eloquent Query for visible staff based on role & hierarchy
     */
    public static function getVisibleStaffQuery($user = null)
    {
        $user = $user ?: auth()->user();
        $query = Admin::where('status', 'active');

        if (!$user) return $query;

        if ($user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        if (self::isSuperAdmin($user)) {
            return $query;
        }

        if (self::isTeamLeader($user)) {
            $subordinateIds = self::getSubordinateUserIds($user);
            return $query->whereIn('id', $subordinateIds);
        }

        // Regular Staff: themselves + any teammates
        $userTeamIds = self::getUserTeamIds($user);
        $teammateIds = [];
        if (!empty($userTeamIds)) {
            $teammateIds = TeamMember::whereIn('team_id', $userTeamIds)->pluck('user_id')->toArray();
        }
        $allowedIds = array_values(array_unique(array_merge([$user->id], $teammateIds)));

        return $query->whereIn('id', $allowedIds);
    }
}
