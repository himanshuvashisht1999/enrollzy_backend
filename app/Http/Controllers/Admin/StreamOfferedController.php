<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StreamOffered;
use Illuminate\Http\Request;

class StreamOfferedController extends Controller
{
    public function index()
    {
        $items = StreamOffered::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.stream-offereds.index', compact('items'));
    }

    public function create()
    {
        return view('admin.stream-offereds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        StreamOffered::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.stream-offereds.index')->with('success', 'Stream Offered created successfully.');
    }

    public function edit(StreamOffered $streamOffered)
    {
        return view('admin.stream-offereds.edit', compact('streamOffered'));
    }

    public function update(Request $request, StreamOffered $streamOffered)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $streamOffered->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.stream-offereds.index')->with('success', 'Stream Offered updated successfully.');
    }

    public function destroy(StreamOffered $streamOffered)
    {
        $streamOffered->delete();
        return redirect()->route('admin.stream-offereds.index')->with('success', 'Stream Offered deleted successfully.');
    }
}
