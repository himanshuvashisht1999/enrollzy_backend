<?php

namespace App\Services;

use App\Models\LogBook;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class LogBookService
{
    public function create($for, $forID, $log, $url = null)
    {
        // in this service we are making all required logs for admin so admin can see the logs of each staff
        try {
            LogBook::create([
                'for' => $for,
                'for_id' => $forID,
                'log' => $log,
                'url' => $url,
                'admin_id' => Auth::guard('admin')->id(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to store project log in table reason : ' . $e->getMessage());
            throw $e;
        }
        return true;
    }
}
