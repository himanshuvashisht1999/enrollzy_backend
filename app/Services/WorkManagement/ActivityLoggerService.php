<?php

namespace App\Services\WorkManagement;

use App\Models\TaskActivityLog;

class ActivityLoggerService
{
    public static function log($actionType, $description, $taskId = null, $projectId = null, $milestoneId = null, $fromState = null, $toState = null, $extraData = null)
    {
        return TaskActivityLog::log($actionType, $description, $taskId, $projectId, $milestoneId, $fromState, $toState, $extraData);
    }
}
