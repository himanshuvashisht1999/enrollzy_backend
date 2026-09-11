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

        $role = strtolower($user->role ?? '');
        return in_array($role, ['superadmin', 'admin'])
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

        // Check role title containing manager / lead
        $role = strtolower($user->role ?? '');
        if (str_contains($role, 'manager') || str_contains($role, 'leader') || str_contains($role, 'lead')) {
            return true;
        }

        return false;
    }

    /**
     * Get all subordinate / team member user IDs for a user
     */
    public static function getSubordinateUserIds($user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) return [];

        $userIds = [$user->id];

        // 1. Direct reports via manager_id in admin table
        $directReports = Admin::where('manager_id', $user->id)->pluck('id')->toArray();
        $userIds = array_merge($userIds, $directReports);

        // 2. Members of teams led by this user
        $ledTeamIds = self::getLedTeamIds($user);
        if (!empty($ledTeamIds)) {
            $teamMemberIds = TeamMember::whereIn('team_id', $ledTeamIds)->pluck('user_id')->toArray();
            $userIds = array_merge($userIds, $teamMemberIds);
        }

        return array_values(array_unique(array_filter($userIds)));
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

        // Tier 2: Team Leader / Manager
        if (self::isTeamLeader($user)) {
            $ledTeamIds = self::getLedTeamIds($user);
            $subordinateIds = self::getSubordinateUserIds($user);

            return $query->where(function ($q) use ($userId, $ledTeamIds, $subordinateIds) {
                // Owned or created by leader
                $q->where('projects.owner_id', $userId)
                  ->orWhere('projects.staff_id', $userId)
                  ->orWhere('projects.created_by', $userId)
                  // Member of project
                  ->orWhereHas('members', function ($mq) use ($userId) {
                      $mq->where('admin.id', $userId);
                  });

                // Associated with teams led by user
                if (!empty($ledTeamIds)) {
                    $q->orWhereIn('projects.team_id', $ledTeamIds);
                }

                // Has tasks belonging to or created by subordinates
                $q->orWhereHas('tasks', function ($tq) use ($subordinateIds, $ledTeamIds) {
                    $tq->whereIn('tasks.assigned_to', $subordinateIds)
                       ->orWhereIn('tasks.created_by', $subordinateIds)
                       ->orWhereIn('tasks.staff_id', $subordinateIds)
                       ->orWhereHas('activeAssignees', function ($aq) use ($subordinateIds) {
                           $aq->whereIn('task_assignees.user_id', $subordinateIds);
                       });

                    if (!empty($ledTeamIds)) {
                        $tq->orWhereIn('tasks.team_id', $ledTeamIds);
                    }
                });
            });
        }

        // Tier 3: Regular Staff -> Only own projects or projects where they are assigned tasks/members
        return $query->where(function ($q) use ($userId) {
            $q->where('projects.owner_id', $userId)
              ->orWhere('projects.staff_id', $userId)
              ->orWhere('projects.created_by', $userId)
              ->orWhereHas('members', function ($mq) use ($userId) {
                  $mq->where('admin.id', $userId);
              })
              ->orWhereHas('tasks', function ($tq) use ($userId) {
                  $tq->where('tasks.assigned_to', $userId)
                     ->orWhere('tasks.created_by', $userId)
                     ->orWhere('tasks.staff_id', $userId)
                     ->orWhereHas('activeAssignees', function ($aq) use ($userId) {
                         $aq->where('task_assignees.user_id', $userId);
                     });
              });
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

        // Tier 1: Super Admin / Admin -> Full Visibility
        if (self::isSuperAdmin($user)) {
            return $query;
        }

        $userId = $user->id;

        // Tier 2: Team Leader / Manager -> Sees their own tasks + all tasks of staff under them
        if (self::isTeamLeader($user)) {
            $subordinateIds = self::getSubordinateUserIds($user);
            $ledTeamIds = self::getLedTeamIds($user);

            return $query->where(function ($q) use ($subordinateIds, $ledTeamIds) {
                $q->whereIn('tasks.assigned_to', $subordinateIds)
                  ->orWhereIn('tasks.created_by', $subordinateIds)
                  ->orWhereIn('tasks.staff_id', $subordinateIds)
                  ->orWhereHas('activeAssignees', function ($aq) use ($subordinateIds) {
                      $aq->whereIn('task_assignees.user_id', $subordinateIds);
                  });

                if (!empty($ledTeamIds)) {
                    $q->orWhereIn('tasks.team_id', $ledTeamIds);
                }
            });
        }

        // Tier 3: Regular Staff -> Sees ONLY their own tasks (assigned to them or created by them)
        return $query->where(function ($q) use ($userId) {
            $q->where('tasks.assigned_to', $userId)
              ->orWhere('tasks.created_by', $userId)
              ->orWhere('tasks.staff_id', $userId)
              ->orWhereHas('activeAssignees', function ($aq) use ($userId) {
                  $aq->where('task_assignees.user_id', $userId);
              });
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

        if (self::isTeamLeader($user)) {
            $subordinateIds = self::getSubordinateUserIds($user);
            $ledTeamIds = self::getLedTeamIds($user);

            return $query->where(function ($q) use ($userId, $subordinateIds, $ledTeamIds) {
                $q->where('milestones.owner_id', $userId)
                  ->orWhere('milestones.lead_user_id', $userId)
                  ->orWhereHas('project', function ($pq) {
                      self::applyProjectScope($pq);
                  })
                  ->orWhereHas('tasks', function ($tq) use ($subordinateIds) {
                      $tq->whereIn('tasks.assigned_to', $subordinateIds)
                         ->orWhereHas('activeAssignees', function ($aq) use ($subordinateIds) {
                             $aq->whereIn('task_assignees.user_id', $subordinateIds);
                         });
                  });

                if (!empty($ledTeamIds)) {
                    $q->orWhereIn('milestones.team_id', $ledTeamIds);
                }
            });
        }

        // Regular Staff
        return $query->where(function ($q) use ($userId) {
            $q->where('milestones.owner_id', $userId)
              ->orWhere('milestones.lead_user_id', $userId)
              ->orWhereHas('project', function ($pq) {
                  self::applyProjectScope($pq);
              })
              ->orWhereHas('tasks', function ($tq) use ($userId) {
                  $tq->where('tasks.assigned_to', $userId)
                     ->orWhere('tasks.created_by', $userId)
                     ->orWhereHas('activeAssignees', function ($aq) use ($userId) {
                         $aq->where('task_assignees.user_id', $userId);
                     });
              });
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
        $userTeamIds = self::getUserTeamIds($user);

        return $query->where(function ($q) use ($userId, $userTeamIds) {
            $q->where('teams.team_leader_id', $userId)
              ->orWhereIn('teams.id', $userTeamIds)
              ->orWhereHas('members', function ($mq) use ($userId) {
                  $mq->where('admin.id', $userId);
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

        if (self::isTeamLeader($user)) {
            $subordinateIds = self::getSubordinateUserIds($user);

            return $query->where(function ($q) use ($userId, $subordinateIds) {
                $q->where('meetings.created_by', $userId)
                  ->orWhereIn('meetings.created_by', $subordinateIds)
                  ->orWhereHas('participants', function ($pq) use ($subordinateIds) {
                      $pq->whereIn('meeting_participants.user_id', $subordinateIds);
                  })
                  ->orWhereHas('project', function ($projQ) use ($user) {
                      self::applyProjectScope($projQ, $user);
                  });
            });
        }

        // Regular Staff
        return $query->where(function ($q) use ($userId, $user) {
            $q->where('meetings.created_by', $userId)
              ->orWhereHas('participants', function ($pq) use ($userId) {
                  $pq->where('meeting_participants.user_id', $userId);
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

        // Regular Staff: only see themselves
        return $query->where('id', $user->id);
    }
}
