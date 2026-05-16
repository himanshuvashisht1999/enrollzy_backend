<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterestedInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\InterestedIn::where('organization_id', auth()->user()->organization_id)->latest();
            return \Yajra\DataTables\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.interested-ins.edit', $row->id) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button onclick="deleteItem(' . $row->id . ')" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('admin.interested_ins.index');
    }

    public function create()
    {
        return view('admin.interested_ins.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        \App\Models\InterestedIn::create([
            'name' => $request->name,
            'status' => $request->status ?? 'active',
            'organization_id' => auth()->user()->organization_id
        ]);
        return redirect()->route('admin.interested-ins.index')->with('success', 'Interest created successfully');
    }

    public function edit($id)
    {
        $interest = \App\Models\InterestedIn::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return view('admin.interested_ins.edit', compact('interest'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $interest = \App\Models\InterestedIn::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $interest->update($request->all());
        return redirect()->route('admin.interested-ins.index')->with('success', 'Interest updated successfully');
    }

    public function destroy($id)
    {
        $interest = \App\Models\InterestedIn::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $interest->delete();
        return response()->json(['status' => 1, 'message' => 'Deleted successfully']);
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $item = \App\Models\InterestedIn::create([
            'name' => $request->name,
            'status' => 'active',
            'organization_id' => auth()->user()->organization_id
        ]);
        return response()->json(['status' => 1, 'data' => $item]);
    }
}


