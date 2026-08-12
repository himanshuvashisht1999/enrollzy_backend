<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CampusTypeNew;

class CampusTypeNewController extends Controller
{
    public function index()
    {
        $types = CampusTypeNew::orderBy('sort_order', 'asc')->get();
        return view('admin.campus_type_new.index', compact('types'));
    }

    public function create()
    {
        return view('admin.campus_type_new.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        CampusTypeNew::create($request->all());

        return redirect()->route('admin.campus_type_new.index')->with('success', 'Campus Type created successfully.');
    }

    public function edit(CampusTypeNew $campusTypeNew)
    {
        return view('admin.campus_type_new.edit', compact('campusTypeNew'));
    }

    public function update(Request $request, CampusTypeNew $campusTypeNew)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $campusTypeNew->update($request->all());

        return redirect()->route('admin.campus_type_new.index')->with('success', 'Campus Type updated successfully.');
    }

    public function destroy(CampusTypeNew $campusTypeNew)
    {
        $campusTypeNew->delete();

        return redirect()->route('admin.campus_type_new.index')->with('success', 'Campus Type deleted successfully.');
    }
}
