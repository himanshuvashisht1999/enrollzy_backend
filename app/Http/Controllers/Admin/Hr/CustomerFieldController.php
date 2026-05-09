<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\CustomerField;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class CustomerFieldController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $organization_id = auth()->user()->organization_id;
            $data = CustomerField::where('organization_id', $organization_id)->orderBy('sequence', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_required', function ($row) {
                    return $row->is_required ? '<span class="badge bg-soft-danger">Required</span>' : '<span class="badge bg-soft-info">Optional</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="'.route('admin.hr.customer-fields.edit', encrypt($row->id)).'" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form action="'.route('admin.hr.customer-fields.destroy', $row->id).'" method="POST" class="ms-1 delete-form">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="button" class="btn btn-sm btn-soft-danger delete-btn"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['is_required', 'status', 'action'])
                ->make(true);
        }

        return view('admin.hr.customers.field.index');
    }

    public function create()
    {
        return view('admin.hr.customers.field.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'status' => 'required',
        ]);

        try {
            CustomerField::create([
                'name' => $request->name,
                'label' => $request->label,
                'is_required' => $request->is_required ?? 0,
                'status' => $request->status,
                'sequence' => $request->sequence ?? 1,
                'organization_id' => auth()->user()->organization_id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.hr.customer-fields.index')->with('success', 'Field added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $field = CustomerField::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return view('admin.hr.customers.field.edit', compact('field'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $field = CustomerField::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'status' => 'required',
        ]);

        try {
            $field->update([
                'name' => $request->name,
                'label' => $request->label,
                'is_required' => $request->is_required ?? 0,
                'status' => $request->status,
                'sequence' => $request->sequence ?? 1,
            ]);

            return redirect()->route('admin.hr.customer-fields.index')->with('success', 'Field updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $field = CustomerField::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $field->delete();
        return redirect()->back()->with('success', 'Field deleted successfully');
    }
}
