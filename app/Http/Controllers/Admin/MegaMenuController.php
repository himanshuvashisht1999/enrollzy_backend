<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderLink;
use App\Models\MegaMenu;
use Illuminate\Http\Request;

class MegaMenuController extends Controller
{
    public function index(Request $request)
    {
        $parentLinks = HeaderLink::whereNull('parent_id')->where('status', 1)->orderBy('sort_order')->get();
        $childLinks = collect();
        $selectedParent = $request->parent_id;
        $selectedChild = $request->child_id;

        if ($selectedParent) {
            $childLinks = HeaderLink::where('parent_id', $selectedParent)->where('status', 1)->orderBy('sort_order')->get();
        }

        $headerLinks = collect();
        if ($selectedChild) {
            $headerLinks = HeaderLink::where('id', $selectedChild)->where('status', 1)->get();
        }

        $subItemsByHeaderLink = collect();
        if ($headerLinks->count() > 0) {
            $subItemsByHeaderLink = MegaMenu::whereNotNull('parent_id')
                ->whereIn('header_link_id', $headerLinks->pluck('id'))
                ->orderBy('sort_order')
                ->get()
                ->groupBy('header_link_id');
        }

        $rootMegaMenus = MegaMenu::whereNull('parent_id')
            ->whereNotNull('header_link_id')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('header_link_id');

        $existingColumnHeadings = MegaMenu::whereNotNull('column_title')
            ->where('column_title', '!=', '')
            ->distinct()
            ->orderBy('column_title')
            ->pluck('column_title');

        $headingsGroupedByHeaderLink = MegaMenu::whereNotNull('parent_id')
            ->whereNotNull('column_title')
            ->where('column_title', '!=', '')
            ->select('header_link_id', 'column_title')
            ->distinct()
            ->get()
            ->groupBy('header_link_id')
            ->map(function ($items) {
                return $items->pluck('column_title')->values();
            });

        return view('admin.mega-menu.index', compact(
            'parentLinks',
            'childLinks',
            'selectedParent',
            'selectedChild',
            'headerLinks',
            'subItemsByHeaderLink',
            'rootMegaMenus',
            'existingColumnHeadings',
            'headingsGroupedByHeaderLink'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'header_link_id' => 'required|exists:header_links,id',
            'column_title'   => 'nullable|string|max:255',
            'title'          => 'required|string|max:255',
            'url'            => 'nullable|string|max:500',
            'sort_order'     => 'nullable|integer',
            'is_highlighted' => 'nullable|boolean',
        ]);

        $rootMenu = MegaMenu::whereNull('parent_id')
            ->where('header_link_id', $request->header_link_id)
            ->first();

        if (!$rootMenu) {
            $headerLink = HeaderLink::findOrFail($request->header_link_id);
            $rootMenu = MegaMenu::create([
                'parent_id'      => null,
                'header_link_id' => $headerLink->id,
                'title'          => $headerLink->title,
                'url'            => $headerLink->url,
                'sort_order'     => $headerLink->sort_order,
                'status'         => 1,
                'is_highlighted' => 0,
            ]);
        }

        MegaMenu::create([
            'parent_id'      => $rootMenu->id,
            'header_link_id' => $request->header_link_id,
            'column_title'   => $request->column_title ?: null,
            'title'          => $request->title,
            'url'            => $request->url ?: null,
            'sort_order'     => $request->sort_order ?? 0,
            'is_highlighted' => $request->has('is_highlighted') ? 1 : 0,
            'status'         => 1,
        ]);

        return redirect()->back()->with('success', 'Sub option added successfully.');
    }

    public function update(Request $request, MegaMenu $megaMenu)
    {
        $request->validate([
            'header_link_id' => 'nullable|exists:header_links,id',
            'column_title'   => 'nullable|string|max:255',
            'title'          => 'required|string|max:255',
            'url'            => 'nullable|string|max:500',
            'sort_order'     => 'nullable|integer',
        ]);

        $data = [
            'header_link_id' => $request->header_link_id ?: $megaMenu->header_link_id,
            'column_title'   => $request->column_title ?: null,
            'title'          => $request->title,
            'url'            => $request->url ?: null,
            'sort_order'     => $request->sort_order ?? 0,
            'is_highlighted' => $request->has('is_highlighted') ? 1 : 0,
        ];

        if ($request->has('status')) {
            $data['status'] = $request->status ? 1 : 0;
        }

        if ($request->header_link_id && $request->header_link_id != $megaMenu->header_link_id) {
            $rootMenu = MegaMenu::whereNull('parent_id')
                ->where('header_link_id', $request->header_link_id)
                ->first();
            if ($rootMenu) {
                $data['parent_id'] = $rootMenu->id;
            }
        }

        $megaMenu->update($data);

        return redirect()->back()->with('success', 'Sub option updated successfully.');
    }

    public function updateStatus(Request $request, MegaMenu $megaMenu)
    {
        $megaMenu->update(['status' => !$megaMenu->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(MegaMenu $megaMenu)
    {
        if (is_null($megaMenu->parent_id)) {
            return redirect()->back()->with('error', 'Root categories are managed from Header Links. Delete sub-options only.');
        }
        $megaMenu->delete();
        return redirect()->back()->with('success', 'Sub option deleted successfully.');
    }
}

