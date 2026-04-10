<?php

namespace App\Http\Controllers\Project;

use Exception;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $data = LeadSource::get();
        }else{
            $data = LeadSource::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';

                    $btn .= '<a href="' . route('admin.leadSource.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.leadSource.destroy', encrypt($row->id)) . '" class="m-0 p-0">
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

        return view('project.leadSource.index');
    }

    public function create()
    {
        return view('project.leadSource.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $createLeadSource = [
            'name' => $request->name,
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            $leadSource = LeadSource::create($createLeadSource);
            return redirect(route('admin.leadSource.index'))->with('success', 'Lead Source added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $leadId = decrypt($id);
        $leadSource = LeadSource::find($leadId);
        if ($leadSource) {
            return view('project.leadSource.edit', compact('leadSource'));
        }
        return redirect()->back()->with('error', 'Lead Source not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $leadSourceId = decrypt($id);
            $lSource = LeadSource::findOrFail($leadSourceId);
            $updateData = $request->only([
                'name',
            ]);
            if ($lSource->update($updateData)) {
                return redirect(route('admin.leadSource.index'))->with('success', 'Lead Source updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $leadId = decrypt($id);
        $delete = LeadSource::find($leadId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Lead Source deleted successfully');
        }
        return redirect()->back()->with('error', 'Lead Source cannot delete');
    }
}
