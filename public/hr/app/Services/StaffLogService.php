<?php

namespace App\Services;

use App\Models\StaffLog;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class StaffLogService
{
    public function log($modal, $modal_id, $type, $log)
    {
        try {
            $logStaff = [
                'staff_id' => Auth::guard('admin')->id(),
                'table' => $modal,
                'primary_id' => $modal_id,
                'type' => $type ?? 'other',
                'log' => Auth::guard('admin')->user()->name . ' ' . $log,
            ];
            StaffLog::create($logStaff);
        } catch (Exception $e) {
            Log::error('Failed to log staff action: ' . $e->getMessage());
            throw $e;
        }
        return true;
    }
}
