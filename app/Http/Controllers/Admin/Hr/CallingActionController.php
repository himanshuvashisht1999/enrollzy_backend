<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CallingAction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class CallingActionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CallingAction::where('organization_id', $organization_id)->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-action" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form action="'.route('admin.students-crm.calling-actions.destroy', $row->id).'" method="POST" class="ms-1 delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.students_crm.calling.action.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            CallingAction::create([
                'name' => $request->name,
                'status' => $request->status,
                'organization_id' => auth()->user()->organization_id,
            ]);

            return response()->json(['status' => 1, 'message' => 'Action added successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $action = CallingAction::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return response()->json(['status' => 1, 'data' => $action]);
    }

    public function update(Request $request, $id)
    {
        $action = CallingAction::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $action->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json(['status' => 1, 'message' => 'Action updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $action = CallingAction::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $action->delete();
        return redirect()->back()->with('success', 'Action deleted successfully');
    }
}


