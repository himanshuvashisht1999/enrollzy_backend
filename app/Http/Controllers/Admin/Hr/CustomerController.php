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
use App\Imports\CustomerImport;
use App\Exports\CustomerSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = Customer::where('organization_id', $organization_id)->with('category');

            if (!$request->filled('filter_name') && !$request->filled('filter_phone')) {
                $data->whereRaw('1 = 0');
            } else {
                if ($request->filled('filter_name')) {
                    $data->where('name', $request->filter_name);
                }
                if ($request->filled('filter_phone')) {
                    $data->where('phone', $request->filter_phone);
                }
            }

            $data = $data->latest();

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
                    $btn .= '<a href="' . route('admin.customers.main.index.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'category_name', 'action'])
                ->make(true);
        }

        return view('admin.customers.index');
    }

    public function create()
    {
        $organization_id = auth()->user()->organization_id;
        $institutes = Institute::where('organization_id', $organization_id)->get();
        $categories = CustomerCategory::where('parent_id', 0)
            ->where('organization_id', $organization_id)
            ->with('childrenRecursive')
            ->get();
        $interested_ins = \App\Models\InterestedIn::where('organization_id', $organization_id)->where('status', 'active')->get();
        $sessions = \App\Models\CustomerSession::where('organization_id', $organization_id)->where('status', 'active')->get();
        
        $fields = CustomerField::where('status', 'active')
            ->where('organization_id', $organization_id)
            ->orderBy('sequence', 'asc')
            ->get();

        return view('admin.customers.create', compact('institutes', 'categories', 'fields', 'interested_ins', 'sessions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id;
            $data['role'] = 'user';
            $data['status'] = $request->status ?? 'active';

            if (($data['sibling_enrolled'] ?? '0') != '1') {
                $data['sibling_name'] = null;
                $data['sibling_age'] = null;
            }

            // Handle Image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                $file->move(public_path('customer_images'), $fileName);
                $data['image'] = 'customer_images/' . $fileName;
            }

            $customer = Customer::create($data);

            // Academic Details
            if ($request->has('academics')) {
                foreach ($request->academics as $exam => $details) {
                    if (!empty($details['board'])) {
                        \App\Models\CustomerAcademicDetail::create([
                            'user_id' => $customer->id,
                            'examination' => $exam,
                            'board_university' => $details['board'],
                            'school_college' => $details['college'],
                            'year' => $details['year'],
                            'percentage' => $details['percentage'],
                        ]);
                    }
                }
            }

            // Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $type => $file) {
                    $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                    $file->move(public_path('customer_docs'), $fileName);
                    $path = 'customer_docs/' . $fileName;
                    
                    \App\Models\CustomerDocument::create([
                        'user_id' => $customer->id,
                        'document_type' => $type,
                        'file_path' => $path,
                    ]);
                }
            }

            // Custom Fields
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

            return redirect()->route('admin.customers.main.index.index')->with('success', 'Customer created successfully');
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
            ->with('childrenRecursive')
            ->get();
        $fields = CustomerField::where('status', 'active')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('sequence', 'asc')
            ->get();
        
        $fieldValues = UserCustomerField::where('user_id', $id)->pluck('value', 'customer_field_id')->toArray();
        $interested_ins = \App\Models\InterestedIn::where('organization_id', auth()->user()->organization_id)->where('status', 'active')->get();
        $sessions = \App\Models\CustomerSession::where('organization_id', auth()->user()->organization_id)->where('status', 'active')->get();

        return view('admin.customers.edit', compact('customer', 'institutes', 'categories', 'fields', 'fieldValues', 'interested_ins', 'sessions'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $customer = Customer::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();

            if (($data['sibling_enrolled'] ?? '0') != '1') {
                $data['sibling_name'] = null;
                $data['sibling_age'] = null;
            }

            // Handle Profile Image
            if ($request->hasFile('image')) {
                if ($customer->image && file_exists(public_path($customer->image))) {
                    @unlink(public_path($customer->image));
                }
                $file = $request->file('image');
                $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                $file->move(public_path('customer_images'), $fileName);
                $data['image'] = 'customer_images/' . $fileName;
            }

            $customer->update($data);

            // Academic Details
            if ($request->has('academics')) {
                foreach ($request->academics as $exam => $details) {
                    if (!empty($details['board'])) {
                        \App\Models\CustomerAcademicDetail::updateOrCreate(
                            ['user_id' => $customer->id, 'examination' => $exam],
                            [
                                'board_university' => $details['board'],
                                'school_college' => $details['college'],
                                'year' => $details['year'],
                                'percentage' => $details['percentage'],
                            ]
                        );
                    }
                }
            }

            // Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $type => $file) {
                    $oldDoc = \App\Models\CustomerDocument::where('user_id', $customer->id)->where('document_type', $type)->first();
                    if ($oldDoc && file_exists(public_path($oldDoc->file_path))) {
                        @unlink(public_path($oldDoc->file_path));
                    }

                    $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                    $file->move(public_path('customer_docs'), $fileName);
                    $path = 'customer_docs/' . $fileName;

                    \App\Models\CustomerDocument::updateOrCreate(
                        ['user_id' => $customer->id, 'document_type' => $type],
                        ['file_path' => $path]
                    );
                }
            }

            // Custom Fields
            if ($request->has('customer_fields')) {
                foreach ($request->customer_fields as $fieldId => $value) {
                    UserCustomerField::updateOrCreate(
                        ['user_id' => $customer->id, 'customer_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }

            return redirect()->route('admin.customers.main.index.index')->with('success', 'Customer updated successfully');
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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            ignore_user_abort(true);
            \Illuminate\Support\Facades\DB::disableQueryLog();

            Excel::import(new CustomerImport(auth()->user()->organization_id), $request->file('file'));
            return response()->json(['status' => 1, 'message' => 'Students imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    public function downloadSample()
    {
        return Excel::download(new CustomerSampleExport, 'students_sample.xlsx');
    }
}
