<?php

namespace App\Http\Controllers\Project;


use Illuminate\Http\Request;
use App\Models\CallingStatus;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Validator;
use Auth;

class CallStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CallingStatus::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';
                    $btn .= '<a href="' . route('admin.call_status.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.call_status.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'created_at', 'action'])
                ->make(true);
        }

        return view('project.call_status.index');
    }

    public function create()
    {
        return view('project.call_status.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_require' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            CallingStatus::create([
                'name' => $request->name,
                'date_require' => $request->date_require,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.call_status.index'))->with('success', 'Calling status added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong. ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $cStatusId = decrypt($id);
        try {
            $status = CallingStatus::findOrFail($cStatusId);
            return view('project.call_status.edit', compact('status'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong. ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_require' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $cStatusId = decrypt($id);
        try {
            $status = CallingStatus::findOrFail($cStatusId);
            $status->update([
                'name' => $request->name,
                'date_require' => $request->date_require,
                'organization_id' => Auth::guard('admin')->user()->organization_id,
            ]);
            return redirect(route('admin.call_status.index'))->with('success', 'Calling status updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong. ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $cStatusId = decrypt($id);
        try {
            $status = CallingStatus::findOrFail($cStatusId);
            $status->delete();
            return redirect()->back()->with('success', 'Calling status deleted successfully')->withInput();
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'something went wrong. ' . $e->getMessage())->withInput();
        }
    }
}
