<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\StaffType;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StaffTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StaffType::query();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('created_at', function ($row) {
                    return '<p class="text-sm">' . date('d M, Y', strtotime($row->created_at)) . '</p>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.staff-types.edit', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-pen text-primary"></i></a>';
                    $btn .= '<a href="javascript:void(0)" onclick="deleteData(\'' . route('admin.hr.staff-types.destroy', encrypt($row->id)) . '\')" class="btn btn-sm btn-danger rounded-circle"><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'status', 'created_at', 'action'])
                ->make(true);
        }
        return view('admin.hr.staff-types.index');
    }

    public function create()
    {
        return view('admin.hr.staff-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        StaffType::create($request->all());

        return redirect()->route('admin.hr.staff-types.index')->with('success', 'Staff Type created successfully.');
    }

    public function edit($id)
    {
        $staffType = StaffType::findOrFail(decrypt($id));
        return view('admin.hr.staff-types.edit', compact('staffType'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $staffType = StaffType::findOrFail(decrypt($id));
        $staffType->update($request->all());

        return redirect()->route('admin.hr.staff-types.index')->with('success', 'Staff Type updated successfully.');
    }

    public function destroy($id)
    {
        $staffType = StaffType::findOrFail(decrypt($id));
        $staffType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Staff Type deleted successfully.',
        ]);
    }
}
