<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\CustomerSession::where('organization_id', auth()->user()->organization_id)->latest();
            return \Yajra\DataTables\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.customer-sessions.edit', $row->id) . '" class="btn btn-sm btn-soft-primary"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button onclick="deleteItem(' . $row->id . ')" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('admin.customer_sessions.index');
    }

    public function create()
    {
        return view('admin.customer_sessions.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        \App\Models\CustomerSession::create([
            'name' => $request->name,
            'status' => $request->status ?? 'active',
            'organization_id' => auth()->user()->organization_id
        ]);
        return redirect()->route('admin.customer-sessions.index')->with('success', 'Session created successfully');
    }

    public function edit($id)
    {
        $session = \App\Models\CustomerSession::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        return view('admin.customer_sessions.edit', compact('session'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $session = \App\Models\CustomerSession::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $session->update($request->all());
        return redirect()->route('admin.customer-sessions.index')->with('success', 'Session updated successfully');
    }

    public function destroy($id)
    {
        $session = \App\Models\CustomerSession::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $session->delete();
        return response()->json(['status' => 1, 'message' => 'Deleted successfully']);
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $item = \App\Models\CustomerSession::create([
            'name' => $request->name,
            'status' => 'active',
            'organization_id' => auth()->user()->organization_id
        ]);
        return response()->json(['status' => 1, 'data' => $item]);
    }
}


