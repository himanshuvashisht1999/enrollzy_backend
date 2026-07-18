<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $page = new Page();
        $page->title = $request->title;
        // Generate a unique slug
        $slug = Str::slug($request->title);
        $count = Page::where('slug', 'LIKE', "{$slug}%")->count();
        $page->slug = $count ? "{$slug}-{$count}" : $slug;
        
        $page->content = $request->content;
        $page->meta_title = $request->meta_title;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;
        $page->status = $request->has('status');
        $page->save();

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $page->title = $request->title;
        
        // Update slug only if title changed significantly or user wants to (here we'll just keep the old slug unless empty)
        if (empty($page->slug) || $request->has('update_slug')) {
            $slug = Str::slug($request->title);
            $count = Page::where('slug', 'LIKE', "{$slug}%")->where('id', '!=', $page->id)->count();
            $page->slug = $count ? "{$slug}-{$count}" : $slug;
        }

        $page->content = $request->content;
        $page->meta_title = $request->meta_title;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;
        $page->status = $request->has('status');
        $page->save();

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }
}
