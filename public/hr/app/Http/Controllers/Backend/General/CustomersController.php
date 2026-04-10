<?php

namespace App\Http\Controllers\Backend\General;

use App\Http\Controllers\Controller;
use App\Mail\VerifiyUserEmail;
use App\Models\CustomerCategories;
use App\Models\EmailEntries;
use App\Models\Institutes;
use App\Models\OtpEntries;
use App\Models\Users;
use App\Models\CustomerField;
use App\Models\UserCustomerField;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

use App\Exports\CustomerSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;

class CustomersController extends Controller
{
    public function CustomerList(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Users::select('id', 'name', 'status', 'email', 'phone','category_id')->orderby('updated_at', 'desc');
            }else{
                $data = Users::select('id', 'name', 'status', 'email', 'phone','category_id')->where('organization_id', Auth::guard('admin')->user()->organization_id)->orderby('updated_at', 'desc');
            }
            
            if ($request->input('name')) {
                $data->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->input('email')) {
                $data->where('email', 'like', '%' . $request->email . '%');
            }
            if ($request->input('phone')) {
                $data->where('phone', 'like', '%' . $request->phone . '%');
            }
            
            $data = $data->get()->take(50);
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $status = GetStatusBadge($row->status); //
                    return $status;
                })
                ->addColumn('category_id', function ($row) {
                    $category_id = $row->category_id; //
                    $cat_data = CustomerCategories::where('id',$category_id)->first();
                    if($cat_data){
                        $cat_name = $cat_data->name;
                    }else{
                        $cat_name = '';
                    }
                    return $cat_name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('customer-edit') || auth()->user()->can('customer-read')) {
                        $btn .= '<a class="btn btn-sm" href="' . route('admin.customer.edit', $row->id) . '"><i
                                class="fa fa-edit text-success"></i></a>';
                    }
                    $btn .= '|</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action','category_id'])
                ->make(true);
        }
        return view('general.customer.index');
    }
    public function createCustomers(Request $request)
    {
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $Institutes = Institutes::get();
            $CustomerCategories = CustomerCategories::where('parent_id',0)->get();
            $CustomerFields = CustomerField::where('status','active11')->orderby('sequence','asc')->get();
        }else{
            $Institutes = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $CustomerCategories = CustomerCategories::where('parent_id',0)->where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $CustomerFields = CustomerField::where('status','active')->where('organization_id', Auth::guard('admin')->user()->organization_id)->orderby('sequence','asc')->get();

        }
        return view('general.customer.create', compact('Institutes', 'CustomerCategories','CustomerFields'));
    }
    public function storeCustomers(Request $request)
    {

        $validator = Validator($request->all(), [
            'name' => 'required',
            // 'email' => 'required',
            'phone' => 'required',
            'category' => 'required',
            //'institute' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $createInstitute = [
                'name' => $request->name,
                'email' => $request->email ?? null,
                'phone' => $request->phone,
                'category_id' => $request->category ?? '',
                'institute_id' => $request->institute ?? 0,
                'status' => $request->status,
                'country' => $request->country ?? '',
                'state' => $request->state ?? '',
                'city' => $request->city,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ];
            $insertResult = Users::create($createInstitute);

            $customerFields = $request->input('customer_fields', []);
            foreach ($customerFields as $fieldId => $value) {
                if ($value === null || $value === '') {
                    continue; // skip empty values if you want
                }

                UserCustomerField::create([
                    'user_id'          => $insertResult->id,
                    'customer_field_id'=> $fieldId,
                    'value'            => $value,
                ]);
            }

            staffLog('institutes', $insertResult->id, 'create', ' institutes created');
            if ($insertResult) {
                return redirect(route('admin.customer.list'))->with('success', 'User added successfully');
            } else {
                return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
            }
        }
    }
    public function editCustomers(Request $request, $id)
    {
        $userData = Users::find($id);

        if (Auth::guard('admin')->user()->role === 'superadmin') {
            $Institutes = Institutes::get();
            $rootCategories = CustomerCategories::where('parent_id', 0)->get();
            $CustomerFields = CustomerField::where('status', 'active11')->orderby('sequence','asc')->get();
        } else {
            $Institutes = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $rootCategories = CustomerCategories::where('parent_id', 0)
                ->where('organization_id', Auth::guard('admin')->user()->organization_id)
                ->get();
            $CustomerFields = CustomerField::where('status', 'active')
                ->where('organization_id', Auth::guard('admin')->user()->organization_id)->orderby('sequence','asc')
                ->get();
        }

        // Build dynamic field values
        $UserCustomerField = UserCustomerField::where('user_id', $id)
            ->pluck('value', 'customer_field_id')
            ->toArray();

        // ---------- Build category levels for edit ----------
        $categoryLevels = [];
        $selectedCategoryId = $userData->category_id;

        if ($selectedCategoryId) {
            // 1) Build chain from leaf up to root
            $chain = [];
            $current = CustomerCategories::find($selectedCategoryId);

            while ($current) {
                $chain[] = $current;
                if ($current->parent_id == 0) {
                    break;
                }
                $current = CustomerCategories::find($current->parent_id);
            }

            // Now: root -> ... -> leaf
            $chain = array_reverse($chain);

            // 2) Level 0 (root) — use rootCategories
            $rootSelectedId = $chain[0]->id ?? null;
            $categoryLevels[] = [
                'categories' => $rootCategories,
                'selected'   => $rootSelectedId,
            ];

            // 3) Remaining levels
            if (count($chain) > 1) {
                for ($i = 1; $i < count($chain); $i++) {
                    $parent = $chain[$i - 1];    // previous in chain
                    $currentNode = $chain[$i];   // selected in this level

                    $siblingsQuery = CustomerCategories::where('parent_id', $parent->id);

                    if (Auth::guard('admin')->user()->role !== 'superadmin') {
                        $siblingsQuery->where('organization_id', Auth::guard('admin')->user()->organization_id);
                    }

                    $siblings = $siblingsQuery->get();

                    $categoryLevels[] = [
                        'categories' => $siblings,
                        'selected'   => $currentNode->id,
                    ];
                }
            }
        } else {
            // No category selected yet → show only root level
            $categoryLevels[] = [
                'categories' => $rootCategories,
                'selected'   => null,
            ];
        }

        return view('general.customer.edit', compact(
            'Institutes',
            'rootCategories',
            'categoryLevels',
            'userData',
            'CustomerFields',
            'UserCustomerField'
        ));
    }

    public function updateCustomers(Request $request, $id)
    {
        $validator = Validator($request->all(), [
            'name' => 'required',
            // 'email' => 'required',
            'phone' => 'required',
            'category' => 'required',
            //'institute' => 'required',
            'status' => 'required',
        ]);
        // Find the user by ID or any unique identifier
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $user = Users::find($id);  // Find the user by ID or any unique identifier
            if ($user) {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email ?? null,
                    'phone' => $request->phone,
                    'category_id' => $request->category,
                    'institute_id' => $request->institute ?? 0,
                    'status' => $request->status,
                    'country' => $request->country ?? '',
                    'state' => $request->state ?? '',
                    'city' => $request->city,
                    'organization_id' => Auth::guard('admin')->user()->organization_id,
                ]);

                $customerFields = $request->input('customer_fields', []);   // [field_id => value]

                foreach ($customerFields as $fieldId => $value) {

                    // Skip only if value not sent
                    if (!isset($value)) {
                        continue;
                    }

                    // Create or update if already exists
                    UserCustomerField::updateOrCreate(
                        [
                            'user_id'          => $user->id,
                            'customer_field_id'=> $fieldId,
                        ],
                        [
                            'value'            => $value,  // Always update / insert
                        ]
                    );
                }

                return redirect(route('admin.customer.list'))->with('success', 'User updated successfully');
            } else {
                return redirect()->back()->with('error', 'User not found');
            }
        }
    }

    public function getChildren(Request $request)
    {
        $parentId = $request->parent_id;

        if (Auth::guard('admin')->user()->role === 'superadmin') {
            $categories = CustomerCategories::where('parent_id', $parentId)
                ->where('status', 'active')
                ->get(['id', 'name']);
        } else {
            $categories = CustomerCategories::where('parent_id', $parentId)
                ->where('organization_id', Auth::guard('admin')->user()->organization_id)
                ->where('status', 'active')
                ->get(['id', 'name']);
        }

        return response()->json([
            'status' => 1,
            'data'   => $categories,
        ]);
    }

    public function downloadCustomerSample(Request $request){

        if (Auth::guard('admin')->user()->role === 'superadmin') {
        // use same logic as createCustomers
        $CustomerFields = CustomerField::where('status','active11')
            ->orderBy('sequence','asc')
            ->get();
    } else {
        $CustomerFields = CustomerField::where('status','active')
            ->where('organization_id', Auth::guard('admin')->user()->organization_id)
            ->orderBy('sequence','asc')
            ->get();
    }

    return Excel::download(
        new CustomerSampleExport($CustomerFields),
        'customer-sample-' . now()->format('Ymd-His') . '.xlsx'
    );
    }

    public function importCustomer(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new CustomersImport, $request->file('file'));

            return redirect()
                ->route('admin.customer.list')
                ->with('success', 'Customers imported successfully from Excel.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error while importing customers: ' . $e->getMessage());
        }

    }
    


    // -----------------------------
    // ---------------------------------Customer Category CRUD start here
    // list customer category function
    public function listCustomerCategory(Request $request)
    {
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $response['categories'] = CustomerCategories::where('status','active')->get();
        }else{
            $response['categories'] = CustomerCategories::where('organization_id', Auth::guard('admin')->user()->organization_id)->where('status','active')->get();
        }

        if ($request->ajax()) {


            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = CustomerCategories::get();
            }else{
                $data = CustomerCategories::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('parent_id', function ($row) {
                    $id = $row->parent_id;
                    $parent = 'No Parent';
                    if($id == 0){
                        $parent = 'No Parent';
                    }else{
                        $data = CustomerCategories::where('id',$id)->first();
                        if($data){
                            $parent = $data->name;
                        }
                    }
                    return $parent;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('customer-category-edit') || auth()->user()->can('customer-category-read')) {
                        $btn .=   '<button type="button" class="btn btn-sm edit_category_btn" value="' . $row->id . '"><i
                        class="fa fa-edit text-success"></i></button> |';
                    }
                    if (auth()->user()->can('customer-category-delete')) {
                        $btn .= '<form method="POST" action="' . route('admin.customer_category.delete', $row->id) . '" class="m-0 p-0">
                            <input name="_method" type="hidden" value="DELETE">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <button type="submit" class="btn btn-sm confirm-button"><i
                            class="fa fa-trash text-danger"></i></button>
                            </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('general.customer.category_list',$response);
    }
    // Save new customer category function
    public function saveCustomerCategory(Request $request)
    {
        $validator = Validator($request->all(), [
            'name' => 'required',
            'status' => 'required',
            'customer_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $createCategory = [
                'name' => $request->name,
                'status' => $request->status,
                'customer_type' => $request->customer_type,
                'parent_id' => $request->parent_id,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ];
            $insertResult = CustomerCategories::create($createCategory);
            staffLog('customer_categories', $insertResult->id, 'create', ' customer categories created');
            if ($insertResult) {
                return response()->json(['status' => 1, 'message' => 'Added category successfully']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong']);
            }
        }
    }
    // get customer category data function
    public function getCustomerCategory(Request $request)
    {
        $getResult = CustomerCategories::find($request->id);
        if ($getResult) {
            return response()->json(['status' => 1, 'data' => $getResult]);
        } else {
            return response()->json(['status' => 0, 'message' => 'Customer category not found']);
        }
    }
    // update customer category function
    public function updateCustomerCategory(Request $request)
    {
        $validator = Validator($request->all(), [
            'cusCatId' => 'required:exists:customer_categories,id,deleted_at,NULL',
            'name' => 'required',
            'customer_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $custCategory = CustomerCategories::find($request->cusCatId);
            $updateCategory = [
                'name' => $request->name,
                'customer_type' => $request->customer_type,
                'parent_id' => $request->parent_id,
                'status' => $request->status,
            ];
            $updateResult = $custCategory->update($updateCategory);
            staffLog('customer_categories', $request->cusCatId, 'update', ' customer categories updated');

            if ($updateResult) {
                return response()->json(['status' => 1, 'message' => 'Category update successfully']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong']);
            }
        }
    }
    // delete customer category function
    public function deleteCustomerCategory($id)
    {
        $validateID = CustomerCategories::find($id);
        if ($validateID) {
            staffLog('customer_categories', $id, 'delete', ' customer categories deleted');
            $validateID->delete();
            return redirect()->back()->with('success', 'Customer Category Deleted Successfully');
        }
        return redirect()->back()->with('error', 'Customer category not found');
    }
    // ---------------------------------Customer Category CRUD end here
    // ---------------------------------Institutes CRUD Start here
    //Institutes List function
    public function listInstitutes(Request $request)
    {
        if ($request->ajax()) {
            $data = Institutes::get();


            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Institutes::get();
            }else{
                $data = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';

                    if (auth()->user()->can('institute-edit') || auth()->user()->can('institute-read')) {
                        $btn .=   '<button type="button" class="btn btn-sm editInstitutebtn" value="' . $row->id . '"><i
                            class="fa fa-edit text-success"></i></button>|';
                    }
                    if (auth()->user()->can('institute-delete')) {
                        $btn .= '<form method="POST" action="' . route('admin.institute.delete', $row->id) . '" class="m-0 p-0">
                            <input name="_method" type="hidden" value="DELETE">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <button type="submit" class="btn btn-sm confirm-button"><i
                            class="fa fa-trash text-danger"></i></button>
                            </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('general.customer.institute');
    }
    // save Institute function
    public function saveInstitutes(Request $request)
    {
        $validator = Validator($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $createInstitute = [
                'name' => $request->name,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ];
            $insertResult = Institutes::create($createInstitute);
            staffLog('institutes', $insertResult->id, 'create', ' institutes created');
            if ($insertResult) {
                return response()->json(['status' => 1, 'message' => 'Added Institute successfully']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong']);
            }
        }
    }
    // get Institute function
    public function getInstitutes(Request $request)
    {
        $validator = Validator($request->all(), [
            'id' => 'required:exists:institutes,id,deleted_at,NULL',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $getResult = Institutes::find($request->id);
            if ($getResult) {
                return response()->json(['status' => 1, 'data' => $getResult]);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong']);
            }
        }
    }
    // update Institute function
    public function updateInstitutes(Request $request)
    {
        $validator = Validator($request->all(), [
            'institute_id' => 'required:exists:institutes,id,deleted_at,NULL',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $updateInstitutes = [
                'name' => $request->name,
            ];
            $updateResult = Institutes::find($request->institute_id);
            $result = $updateResult->update($updateInstitutes);
            if ($result) {
                staffLog('institutes', $updateResult->id, 'update', ' institutes updated');
                return response()->json(['status' => 1, 'message' => 'Institute updated successfully']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Something went wrong']);
            }
        }
    }
    // delete Institute function
    public function deleteInstitutes($id)
    {
        $delete = Institutes::find($id);
        if ($delete) {
            staffLog('institutes', $id, 'delete', ' institutes deleted');
            $delete->delete();
            return redirect()->back()->with('success', 'Institute Deleted Successfully');
        }
        return redirect()->back()->with('error', 'Institute not found');
    }

    // ---------------------------------------------
    // find cutomer function
    public function findCustomerForBooking(Request $request)
    {
        $phone = $request->term;
        $customers = Users::where('phone', 'like', '%' . $phone . '%')
            ->where('role', 'user')
            ->orderBy('id', 'desc')->get();
        $item = [];
        if (count($customers) > 0) {
            foreach ($customers as $customer) {
                $data = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'name' => $customer->name,
                    'father_name' => $customer->father_name,
                    'tehsil' => $customer->tehsil,
                    'user_id' => $customer->id,
                    'categoryId' => $customer->categoryid,
                    'instituteId' => $customer->instituteid,
                    'address' => $customer->address,
                    'district' => $customer->district,
                    'landmark' => $customer->landmark,
                    'pincode' => $customer->pincode,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'gstin' => $customer->gstin,
                    'user_type' => $customer->user_type,
                );
                array_push($item, $data);
            }
        } else {
            $data = array(
                'phone' => '-------------',
                'name' => 'Customer not found,',
                'email' => '-----',
            );
            array_push($item, $data);
        }
        return json_encode($item);
    }

    ////////////////////// customer fields

    public function CustomerFieldList(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = CustomerField::whereIn('status',['active','inactive'])->select('id', 'name', 'status', 'label', 'is_required','sequence')->orderby('sequence', 'asc');
            }else{
                $data = CustomerField::whereIn('status',['active','inactive'])->select('id', 'name', 'status', 'label', 'is_required','sequence')->where('organization_id', Auth::guard('admin')->user()->organization_id)->orderby('sequence', 'asc');
            }
            
            if ($request->input('name')) {
                $data->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->input('label')) {
                $data->where('label', 'like', '%' . $request->label . '%');
            }

            $data = $data->get()->take(50);
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $status = GetStatusBadge($row->status); //
                    return $status;
                })
                ->addColumn('is_required', function ($row) {
                    $is_required = $row->is_required;
                    return $is_required == 1 ? 'Yes' : 'No';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('customer-edit') || auth()->user()->can('customer-read')) {
                        $btn .= '<a class="btn btn-sm" href="' . route('admin.customer_field.edit', $row->id) . '"><i
                                class="fa fa-edit text-success"></i></a> | ';
                    }
                    if (auth()->user()->can('customer-category-delete')) {
                        $btn .= '<form method="POST" action="' . route('admin.customer_field.delete', $row->id) . '" class="m-0 p-0">
                            <input name="_method" type="hidden" value="DELETE">
                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                            <button type="submit" class="btn btn-sm confirm-button"><i
                            class="fa fa-trash text-danger"></i></button>
                            </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status','is_required', 'action'])
                ->make(true);
                
        }
        return view('general.customer_field.index');
    }
    public function deleteCustomerField($id)
    {
        $validateID = CustomerField::find($id);
        if ($validateID) {
            $validateID->status = 'deleted'; ///deleted 
            $validateID->save(); 
            staffLog('customer_categories', $id, 'delete', ' customer field deleted');
            // $validateID->delete();
            return redirect()->back()->with('success', 'Customer Field Deleted Successfully');
        }
        return redirect()->back()->with('error', 'Customer Field not found');
    }
    public function createCustomerField(Request $request)
    {
      
        return view('general.customer_field.create');
    }
    public function storeCustomerField(Request $request)
    {

        $validator = Validator($request->all(), [
            'name' => 'required',
            'label' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $createData = [
                'name' => $request->name,
                'label' => $request->label,
                'is_required' => $request->is_required ?? 0,
                'status' => $request->status,
                'sequence' => $request->sequence ?? 1,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
                'user_id' => Auth::guard('admin')->user()->id,
            ];
            $insertResult = CustomerField::create($createData);
            
            staffLog('customer_field', $insertResult->id, 'create', ' customer field created');
            if ($insertResult) {
                return redirect(route('admin.customer_field.list'))->with('success', 'Customer Field added successfully');
            } else {
                return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
            }
        }
    }
    public function editCustomerField(Request $request, $id)
    {
        $data = CustomerField::find($id);
        return view('general.customer_field.edit', compact('data'));
    }
    public function updateCustomerField(Request $request, $id)
    {
        $validator = Validator($request->all(), [
            'name' => 'required',
            'label' => 'required',
            'is_required' => 'required',
            'status' => 'required',
        ]);
        // Find the user by ID or any unique identifier
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $customer_field = CustomerField::find($id);  // Find the customer_field by ID or any unique identifier
            if ($customer_field) {
                $customer_field->update([
                    'name' => $request->name,
                    'label' => $request->label,
                    'is_required' => $request->is_required ?? 0,
                    'status' => $request->status,
                    'sequence' => $request->sequence ?? 1,
                    'user_id' => Auth::guard('admin')->user()->id,
                    // 'organization_id' => Auth::guard('admin')->user()->organization_id,
                ]);
                return redirect(route('admin.customer_field.list'))->with('success', 'User updated successfully');
            } else {
                return redirect()->back()->with('error', 'User not found');
            }
        }
    }
}
