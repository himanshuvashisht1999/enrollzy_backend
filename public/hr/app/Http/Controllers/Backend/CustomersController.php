<?php

namespace App\Http\Controllers\Backend\General;

use App\Http\Controllers\Controller;
use App\Mail\VerifiyUserEmail;
use App\Models\CustomerCategories;
use App\Models\EmailEntries;
use App\Models\Institutes;
use App\Models\OtpEntries;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class CustomersController extends Controller
{
    public function CustomerList(Request $request)
    {
        if ($request->ajax()) {
            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Users::select('id', 'name', 'status', 'email', 'phone')->orderby('updated_at', 'desc');
            }else{
                $data = Users::select('id', 'name', 'status', 'email', 'phone')->where('organization_id', Auth::guard('admin')->user()->organization_id)->orderby('updated_at', 'desc');
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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    if (auth()->user()->can('customer-edit') || auth()->user()->can('customer-read')) {
                        $btn .= '<a class="btn btn-sm" href="' . route('admin.customer.edit', $row->id) . '"><i
                                class="fa fa-edit text-success"></i></a>';
                    }
                    $btn .= '|</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('general.customer.index');
    }
    public function createCustomers(Request $request)
    {
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $Institutes = Institutes::get();
            $CustomerCategories = CustomerCategories::get();
        }else{
            $Institutes = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $CustomerCategories = CustomerCategories::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('general.customer.create', compact('Institutes', 'CustomerCategories'));
    }
    public function storeCustomers(Request $request)
    {

        $validator = Validator($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'category' => 'required',
            'institute' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        } else {
            $createInstitute = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'category_id' => $request->category,
                'institute_id' => $request->institute,
                'status' => $request->status,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ];
            $insertResult = Users::create($createInstitute);
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
        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $Institutes = Institutes::get();
            $CustomerCategories = CustomerCategories::get();
        }else{
            $Institutes = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $CustomerCategories = CustomerCategories::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
        return view('general.customer.edit', compact('Institutes', 'CustomerCategories','userData'));
    }
    public function updateCustomers(Request $request, $id)
    {
        $validator = Validator($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'category' => 'required',
            'institute' => 'required',
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
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'category_id' => $request->category,
                    'institute_id' => $request->institute,
                    'status' => $request->status,
                    'organization_id' => Auth::guard('admin')->user()->organization_id,
                ]);
                return redirect(route('admin.customer.list'))->with('success', 'User updated successfully');
            } else {
                return redirect()->back()->with('error', 'User not found');
            }
        }
    }


    // -----------------------------
    // ---------------------------------Customer Category CRUD start here
    // list customer category function
    public function listCustomerCategory(Request $request)
    {
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
        return view('general.customer.category_list');
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
}
