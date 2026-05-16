<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CallingStatus;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class CallingStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingStatus::where('organization_id', $organization_id)->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date_require', function ($row) {
                    return $row->date_require === 'yes' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-status" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form action="'.route('admin.students-crm.calling-statuses.destroy', $row->id).'" method="POST" class="ms-1 delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['date_require', 'action'])
                ->make(true);
        }

        return view('admin.students_crm.calling.status.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_require' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            CallingStatus::create([
                'name' => $request->name,
                'date_require' => $request->date_require,
                'organization_id' => auth()->user()->organization_id,
            ]);

            return response()->json(['status' => 1, 'message' => 'Status added successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $status = CallingStatus::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return response()->json(['status' => 1, 'data' => $status]);
    }

    public function update(Request $request, $id)
    {
        $status = CallingStatus::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_require' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $status->update([
                'name' => $request->name,
                'date_require' => $request->date_require,
            ]);

            return response()->json(['status' => 1, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $status = CallingStatus::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $status->delete();
        return redirect()->back()->with('success', 'Status deleted successfully');
    }
}


