<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Exception;

class BanksController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            if($user->is_admin && !isset($user->organization_id)){
                $data = Bank::orderBy('created_at', 'desc')->get();
            }else{
                $data = Bank::where('organization_id', $user->organization_id)->orderBy('created_at', 'desc')->get();
            }

            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return '<p class="text-sm fw-bold mb-0">' . $row->name . '</p>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.hr.banks.edit', encrypt($row->id)) . '" class="btn btn-sm btn-light rounded-circle me-1"><i class="fa fa-edit text-success"></i></a>';
                    
                    $btn .= '<form method="POST" action="' . route('admin.hr.banks.destroy', encrypt($row->id)) . '" class="m-0 p-0 d-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm btn-light rounded-circle confirm-button"><i class="fa fa-trash text-danger"></i></button>
                    </form>';
                    
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
        }
        return view('admin.hr.banks.index');
    }

    public function create()
    {
        return view('admin.hr.banks.create');
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
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect(route('admin.hr.banks.index'))->with('success', 'Bank Account added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $bankId = decrypt($id);
        try {
            $banks = Bank::findOrFail($bankId);
            return view('admin.hr.banks.edit', compact('banks'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Bank not found.');
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
        $bankId = decrypt($id);
        try {
            $bank = Bank::findOrFail($bankId);
            $bank->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return redirect(route('admin.hr.banks.index'))->with('success', 'Bank Account updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $bankId = decrypt($id);
        try {
            $bank = Bank::findOrFail($bankId);
            $bank->delete();
            return redirect(route('admin.hr.banks.index'))->with('success', 'Bank Account deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }
}
