<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderLink;
use Illuminate\Http\Request;

class HeaderLinkController extends Controller
{
    public function index()
    {
        $items = HeaderLink::with('parent')->orderBy('sort_order')->orderBy('title')->get();
        return view('admin.header-links.index', compact('items'));
    }

    public function create()
    {
        $parents = HeaderLink::whereNull('parent_id')->orderBy('title')->get();
        return view('admin.header-links.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:header_links,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        HeaderLink::create([
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'url' => $request->url,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.header-links.index')->with('success', 'Header Link created successfully.');
    }

    public function edit(HeaderLink $headerLink)
    {
        $parents = HeaderLink::whereNull('parent_id')->where('id', '!=', $headerLink->id)->orderBy('title')->get();
        return view('admin.header-links.edit', compact('headerLink', 'parents'));
    }

    public function update(Request $request, HeaderLink $headerLink)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:header_links,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $headerLink->update([
            'parent_id' => $request->parent_id,
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

    public function toggleStatus(HeaderLink $headerLink)
    {
        $headerLink->status = !$headerLink->status;
        $headerLink->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $headerLink->status,
                'message' => $headerLink->status ? 'Header link published successfully.' : 'Header link unpublished successfully.'
            ]);
        }

        $message = $headerLink->status ? 'Header link published successfully.' : 'Header link unpublished successfully.';
        return back()->with('success', $message);
    }
}

