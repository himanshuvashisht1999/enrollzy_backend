<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Admin;

class HrAjaxController extends Controller
{
    public function getDesignations(Request $request)
    {
        $departmentIds = $request->department_ids;
        if (!is_array($departmentIds)) {
            $departmentIds = explode(',', $departmentIds);
        }

        $designations = Designation::whereIn('department_id', $departmentIds)->get(['id', 'name']);
        return response()->json($designations);
    }

    public function getUsers(Request $request)
    {
        $designationIds = $request->designation_ids;
        if (!is_array($designationIds)) {
            $designationIds = explode(',', $designationIds);
        }

        $users = Admin::whereIn('designation_id', $designationIds)->get(['id', 'name']);
        return response()->json($users);
    }
}
