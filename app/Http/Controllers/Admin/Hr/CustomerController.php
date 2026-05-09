<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerField;
use App\Models\UserCustomerField;
use App\Models\Institute;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Customer::where('organization_id', $organization_id)
                ->with('category')
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category->name ?? '<span class="text-muted">No Category</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.customers.index.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'category_name', 'action'])
                ->make(true);
        }

        return view('admin.hr.customers.index');
    }

    public function create()
    {
        $organization_id = auth()->user()->organization_id;
        $institutes = Institute::where('organization_id', $organization_id)->get();
        $categories = CustomerCategory::where('parent_id', 0)
            ->where('organization_id', $organization_id)
            ->get();
        $fields = CustomerField::where('status', 'active')
            ->where('organization_id', $organization_id)
            ->orderBy('sequence', 'asc')
            ->get();

        return view('admin.hr.customers.create', compact('institutes', 'categories', 'fields'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'category_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id;
            $data['role'] = 'user'; // Ensure role is 'user' for customers

            $customer = Customer::create($data);

            if ($request->has('customer_fields')) {
                foreach ($request->customer_fields as $fieldId => $value) {
                    if (!is_null($value)) {
                        UserCustomerField::create([
                            'user_id' => $customer->id,
                            'customer_field_id' => $fieldId,
                            'value' => $value,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.hr.customers.index.index')->with('success', 'Customer created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $customer = Customer::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $institutes = Institute::where('organization_id', auth()->user()->organization_id)->get();
        $categories = CustomerCategory::where('parent_id', 0)
            ->where('organization_id', auth()->user()->organization_id)
            ->get();
        $fields = CustomerField::where('status', 'active')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('sequence', 'asc')
            ->get();
        
        $fieldValues = UserCustomerField::where('user_id', $id)->pluck('value', 'customer_field_id')->toArray();

        return view('admin.hr.customers.edit', compact('customer', 'institutes', 'categories', 'fields', 'fieldValues'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $customer = Customer::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $customer->update($request->all());

            if ($request->has('customer_fields')) {
                foreach ($request->customer_fields as $fieldId => $value) {
                    UserCustomerField::updateOrCreate(
                        ['user_id' => $customer->id, 'customer_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }

            return redirect()->route('admin.hr.customers.index.index')->with('success', 'Customer updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getCategories(Request $request)
    {
        $parentId = $request->parent_id;
        $categories = CustomerCategory::where('parent_id', $parentId)
            ->where('organization_id', auth()->user()->organization_id)
            ->where('status', 'active')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 1,
            'data' => $categories,
        ]);
    }
}
