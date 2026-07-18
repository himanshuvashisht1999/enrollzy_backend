<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class CustomerCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CustomerCategory::where('organization_id', $organization_id)->with('parent')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('parent_name', function ($row) {
                    return $row->parent->name ?? '<span class="text-muted">No Parent</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-soft-primary edit-category" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>';
                    $btn .= '<form action="'.route('admin.customer-categories.destroy', $row->id).'" method="POST" class="ms-1 delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['parent_name', 'status', 'action'])
                ->make(true);
        }

        $organization_id = auth()->user()->organization_id;
        $parentCategories = CustomerCategory::where('organization_id', $organization_id)
            ->where('parent_id', 0)
            ->where('status', 'active')
            ->get();

        return view('admin.customer_categories.index', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required',
            'customer_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            CustomerCategory::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?? 0,
                'customer_type' => $request->customer_type,
                'status' => $request->status,
                'organization_id' => auth()->user()->organization_id,
            ]);

            return response()->json(['status' => 1, 'message' => 'Category added successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $category = CustomerCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return response()->json(['status' => 1, 'data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = CustomerCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'customer_type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            $category->update([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?? 0,
                'customer_type' => $request->customer_type,
                'status' => $request->status,
            ]);

            return response()->json(['status' => 1, 'message' => 'Category updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $category = CustomerCategory::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $item = CustomerCategory::create([
            'name' => $request->name,
            'parent_id' => 0,
            'customer_type' => 'student', // Default to student
            'status' => 'active',
            'organization_id' => auth()->user()->organization_id,
        ]);
        return response()->json(['status' => 1, 'data' => $item]);
    }
}


