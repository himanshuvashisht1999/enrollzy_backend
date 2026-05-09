<?php

use App\Models\HrSetting;
use Illuminate\Support\Facades\Auth;

if (!function_exists('GlobalSetting')) {
    function GlobalSetting($key)
    {
        $orgId = auth()->user()->organization_id ?? null;
        $query = HrSetting::where('option', $key);
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }
        $value = $query->first();
        return $value->value ?? null;
    }
}

if (!function_exists('GetStatusBadge')) {
    function GetStatusBadge($status)
    {
        $status = strtolower($status);
        if ($status == 'active' || $status == 'approved' || $status == 'verified' || $status == 'complete' || $status == 'publish' || $status == 'paid') {
            return '<span class="badge bg-success">' . ucfirst($status) . ' </span>';
        } elseif ($status == 'inactive' || $status == 'cancelled' || $status == 'rejected' || $status == 'unpaid' || $status == 'cancel') {
            return '<span class="badge bg-danger">' . ucfirst($status) . ' </span>';
        } elseif ($status == 'pending' || $status == 'requested' || $status == 'hold' || $status == 'on_hold') {
            return '<span class="badge bg-warning text-dark">' . ucfirst($status) . ' </span>';
        } elseif ($status == 'processing' || $status == 'in_progress' || $status == 'open') {
            return '<span class="badge bg-primary">' . ucfirst($status) . ' </span>';
        } else {
            return '<span class="badge bg-secondary">' . ucfirst($status) . ' </span>';
        }
    }
}

if (!function_exists('encrypt')) {
    function encrypt($id)
    {
        return \Illuminate\Support\Facades\Crypt::encryptString($id);
    }
}

if (!function_exists('decrypt')) {
    function decrypt($id)
    {
        return \Illuminate\Support\Facades\Crypt::decryptString($id);
    }
}
