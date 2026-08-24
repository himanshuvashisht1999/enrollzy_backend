<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\LeadQuality;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LeadQualityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = LeadQuality::where('organization_id', $organization_id)->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input status-toggle" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="d-flex justify-content-center gap-2">';
                    
                    if(auth()->user()->can('lead-quality-edit')){
                        $actionBtn .= '<button type="button" class="btn btn-primary btn-sm edit-btn" 
                                    data-id="' . $row->id . '"
                                    data-name="' . $row->name . '"
                                    data-status="' . $row->status . '">
                                <i class="fas fa-edit"></i>
                            </button>';
                    }

                    if(auth()->user()->can('lead-quality-delete')){
                        $actionBtn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '">
                                <i class="fas fa-trash"></i>
                            </button>';
                    }
                    
                    $actionBtn .= '</div>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.students_crm.calling.lead_quality.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $organization_id = auth()->user()->organization_id;
            
            $existing = LeadQuality::where('name', $request->name)
                                     ->where('organization_id', $organization_id)
                                     ->first();

            if ($existing) {
                return response()->json(['status' => 0, 'message' => 'A Lead Quality with this name already exists.']);
            }

            LeadQuality::create([
                'name' => $request->name,
                'status' => $request->status,
                'organization_id' => $organization_id
            ]);

            return response()->json(['status' => 1, 'message' => 'Lead Quality created successfully']);
        } catch (\Exception $e) {
            Log::error('Error creating Lead Quality: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong']);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $leadQuality = LeadQuality::findOrFail($id);
            $organization_id = auth()->user()->organization_id;

            $existing = LeadQuality::where('name', $request->name)
                                     ->where('organization_id', $organization_id)
                                     ->where('id', '!=', $id)
                                     ->first();

            if ($existing) {
                return response()->json(['status' => 0, 'message' => 'A Lead Quality with this name already exists.']);
            }

            $leadQuality->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json(['status' => 1, 'message' => 'Lead Quality updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating Lead Quality: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong']);
        }
    }

    public function toggleStatus(Request $request)
    {
        try {
            $leadQuality = LeadQuality::findOrFail($request->id);
            $leadQuality->status = $request->status;
            $leadQuality->save();
            
            return response()->json(['status' => 1, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong']);
        }
    }

    public function destroy($id)
    {
        try {
            $leadQuality = LeadQuality::findOrFail($id);
            $leadQuality->delete();

            return response()->json(['status' => 1, 'message' => 'Lead Quality deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong']);
        }
    }
}
