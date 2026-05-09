<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = LeadSource::where('organization_id', $organization_id)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.hr.projects.lead-sources.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.hr.projects.lead-sources.destroy', encrypt($row->id)) . '" class="ms-1 delete-form">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.hr.projects.lead_source.index');
    }

    public function create()
    {
        return view('admin.hr.projects.lead_source.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            LeadSource::create([
                'name' => $request->name,
                'organization_id' => auth()->user()->organization_id,
            ]);
            return redirect()->route('admin.hr.projects.lead-sources.index')->with('success', 'Lead Source added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $leadSource = LeadSource::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return view('admin.hr.projects.lead_source.edit', compact('leadSource'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $leadSource = LeadSource::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $leadSource->update(['name' => $request->name]);
            return redirect()->route('admin.hr.projects.lead-sources.index')->with('success', 'Lead Source updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $leadSource = LeadSource::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $leadSource->delete();
        return redirect()->back()->with('success', 'Lead Source deleted successfully');
    }
}
