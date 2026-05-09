<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class InstituteController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Institute::where('organization_id', $organization_id)->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-institute" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form action="'.route('admin.hr.institutes.destroy', $row->id).'" method="POST" class="ms-1 delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->make(true);
        }

        return view('admin.hr.customers.institute.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            Institute::create([
                'name' => $request->name,
                'organization_id' => auth()->user()->organization_id,
            ]);

            return response()->json(['status' => 1, 'message' => 'Institute added successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $institute = Institute::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return response()->json(['status' => 1, 'data' => $institute]);
    }

    public function update(Request $request, $id)
    {
        $institute = Institute::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $institute->update(['name' => $request->name]);
            return response()->json(['status' => 1, 'message' => 'Institute updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $institute = Institute::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $institute->delete();
        return redirect()->back()->with('success', 'Institute deleted successfully');
    }
}
