<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeaderLink;
use App\Models\MegaMenu;
use Illuminate\Http\Request;

class MegaMenuController extends Controller
{
    /**
     * Show the mega menu manager.
     * Main categories = active header_links (not mega_menu root items).
     * Sub items = mega_menu records where parent_id is not null.
     */
    public function index()
    {
        // Main categories come from header_links table
        $headerLinks = HeaderLink::where('status', 1)->orderBy('sort_order')->get();

        // For each header_link, load its sub-items from mega_menus
        // Sub-items are mega_menu records linked via header_link_id and have a parent_id (root entry)
        // We load by header_link_id directly to group them
        $subItemsByHeaderLink = MegaMenu::whereNotNull('parent_id')
            ->whereNotNull('header_link_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('header_link_id');

        // Also load root-level mega_menu entries (to know their IDs for parent_id assignment in store)
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
            'headerLinks',
            'subItemsByHeaderLink',
            'rootMegaMenus',
            'existingColumnHeadings',
            'headingsGroupedByHeaderLink'
        ));
    }

    /**
     * Store a new sub-item. Parent = root mega_menu for the given header_link_id.
     */
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

        // Find or auto-create the root mega_menu entry for this header_link
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

        // If parent category (header_link_id) was changed, update parent_id to point to new root mega_menu
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
        // Don't allow deleting root entries (those are auto-managed from header_links)
        if (is_null($megaMenu->parent_id)) {
            return redirect()->back()->with('error', 'Root categories are managed from Header Links. Delete sub-options only.');
        }
        $megaMenu->delete();
        return redirect()->back()->with('success', 'Sub option deleted successfully.');
    }
}
