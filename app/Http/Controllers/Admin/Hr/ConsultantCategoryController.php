<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\ConsultantCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultantCategoryController extends Controller
{
    public function index(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $categories = ConsultantCategory::where('parent_id', 0)
            ->where('organization_id', $organization_id)
            ->with('children')
            ->get();
        return view('admin.consultant_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            ConsultantCategory::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?? 0,
                'status' => 'active',
                'organization_id' => auth()->user()->organization_id,
            ]);

            return redirect()->back()->with('success', 'Category added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $category = ConsultantCategory::findOrFail($id);
            $category->delete();
            return redirect()->back()->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
