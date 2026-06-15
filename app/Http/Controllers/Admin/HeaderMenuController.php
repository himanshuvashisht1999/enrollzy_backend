<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderMenu;
use Illuminate\Http\Request;

class HeaderMenuController extends Controller
{
    public function index()
    {
        // Fetch top level menus (parent_id is null)
        $menus = HeaderMenu::with('children.children')->whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.header_menus.index', compact('menus'));
    }

    public function create()
    {
        $parentMenus = HeaderMenu::whereNull('parent_id')->orWhereHas('parent', function($q) {
            $q->whereNull('parent_id');
        })->get(); // Allow nesting up to 2 levels deep (grandparents)
        return view('admin.header_menus.create', compact('parentMenus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:header_menus,id',
            'sort_order' => 'integer',
        ]);

        HeaderMenu::create([
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.header-menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(HeaderMenu $headerMenu)
    {
        $parentMenus = HeaderMenu::where('id', '!=', $headerMenu->id)
            ->where(function($query) use ($headerMenu) {
                $query->whereNull('parent_id')
                      ->orWhereHas('parent', function($q) {
                          $q->whereNull('parent_id');
                      });
            })->get();
            
        return view('admin.header_menus.edit', compact('headerMenu', 'parentMenus'));
    }

    public function update(Request $request, HeaderMenu $headerMenu)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:header_menus,id',
            'sort_order' => 'integer',
        ]);

        // Prevent self-referencing
        if($request->parent_id == $headerMenu->id) {
            return back()->with('error', 'A menu cannot be its own parent.');
        }

        $headerMenu->update([
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.header-menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(HeaderMenu $headerMenu)
    {
        $headerMenu->delete();
        return redirect()->route('admin.header-menus.index')->with('success', 'Menu deleted successfully.');
    }
}
