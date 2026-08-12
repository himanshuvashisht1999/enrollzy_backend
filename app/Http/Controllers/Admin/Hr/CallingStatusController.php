<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CallingStatus;
use App\Models\CallingAction;
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
                ->addColumn('is_more_details', function ($row) {
                    return $row->is_more_details === 'yes' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
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
                ->rawColumns(['date_require', 'is_more_details', 'action'])
                ->make(true);
        }
        $actions = CallingAction::where('organization_id', auth()->user()->organization_id)->where('status', 1)->get();
        return view('admin.students_crm.calling.status.index', compact('actions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_require' => 'required|in:yes,no',
            'is_more_details' => 'required|in:yes,no',
            'calling_action_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            CallingStatus::create([
                'name' => $request->name,
                'date_require' => $request->date_require,
                'is_more_details' => $request->is_more_details,
                'calling_action_id' => $request->calling_action_id,
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
            'is_more_details' => 'required|in:yes,no',
            'calling_action_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $status->update([
                'name' => $request->name,
                'date_require' => $request->date_require,
                'is_more_details' => $request->is_more_details,
                'calling_action_id' => $request->calling_action_id,
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


