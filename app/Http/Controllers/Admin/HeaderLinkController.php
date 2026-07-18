<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderLink;
use Illuminate\Http\Request;

class HeaderLinkController extends Controller
{
    public function index()
    {
        $items = HeaderLink::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.header-links.index', compact('items'));
    }

    public function create()
    {
        return view('admin.header-links.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        HeaderLink::create([
            'title' => $request->title,
            'url' => $request->url,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.header-links.index')->with('success', 'Header Link created successfully.');
    }

    public function edit(HeaderLink $headerLink)
    {
        return view('admin.header-links.edit', compact('headerLink'));
    }

    public function update(Request $request, HeaderLink $headerLink)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $headerLink->update([
            'title' => $request->title,
            'url' => $request->url,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.header-links.index')->with('success', 'Header Link updated successfully.');
    }

    public function destroy(HeaderLink $headerLink)
    {
        $headerLink->delete();
        return redirect()->route('admin.header-links.index')->with('success', 'Header Link deleted successfully.');
    }
}
