<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BanksController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->ajax()) {

                if(Auth::guard('admin')->user()->role === 'superadmin'){
                    $data = Bank::orderBy('created_at', 'desc')->get();
                }else{
                    $data = Bank::where('organization_id', Auth::guard('admin')->user()->organization_id)->orderBy('created_at', 'desc')->get();
                }

            
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('status', function ($row) {
                    $status = GetStatusBadge($row->status); //
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.banks.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                            class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';
                    $btn .= '<form method="POST" action="' . route('admin.banks.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'status','action'])
                ->make(true);
        }
        return view('hr.banks.index');
    }
    public function create()
    {
        return view('hr.banks.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            Bank::create([
                'name' => $request->name,
                'status' => $request->status,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.banks.index'))->with('success', 'Leave applied successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $leaveId = decrypt($id);
        try {
            $banks = Bank::find($leaveId);
            return view('hr.banks.edit', compact('banks'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $leaveId = decrypt($id);
        try {
            $exLeave = Bank::findOrFail($leaveId);
            $exLeave->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return redirect(route('admin.banks.index'))->with('success', 'Bank Account updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }


    public function destroy($id)
    {
        $hID = decrypt($id);
        try {
            $holiday = Bank::findOrFail($hID);
            $holiday->delete();
            return redirect(route('admin.banks.index'))->with('success', 'Bank Account deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong.  ' . $e->getMessage())->withInput();
        }
    }
}
