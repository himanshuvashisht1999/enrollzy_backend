<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterMenu;
use App\Models\Setting;
use App\Models\GeneralLink;
use Illuminate\Http\Request;

class FooterSetupController extends Controller
{
    public function index()
    {
        $setting = Setting::first() ?? new Setting();
        $menus = FooterMenu::with('children')->whereNull('parent_id')->orderBy('sort_order')->get();
        $generalLinks = GeneralLink::orderBy('sort_order')->get();
        return view('admin.footer_setup.index', compact('menus', 'setting', 'generalLinks'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'footer_description' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'footer_text' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $setting = Setting::first() ?? new Setting();
        $setting->fill($request->only([
            'footer_description', 'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',
            'footer_text', 'contact_email', 'contact_phone', 'address'
        ]));
        $setting->save();

        return back()->with('success', 'Footer settings updated successfully.');
    }

    public function createMenu()
    {
        $parentMenus = FooterMenu::whereNull('parent_id')->get();
        return view('admin.footer_setup.create', compact('parentMenus'));
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:footer_menus,id',
            'sort_order' => 'integer',
        ]);

        FooterMenu::create([
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.footer-setup.index')->with('success', 'Footer menu created successfully.');
    }

    public function editMenu(FooterMenu $footerMenu)
    {
        $parentMenus = FooterMenu::whereNull('parent_id')->where('id', '!=', $footerMenu->id)->get();
        return view('admin.footer_setup.edit', compact('footerMenu', 'parentMenus'));
    }

    public function updateMenu(Request $request, FooterMenu $footerMenu)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:footer_menus,id',
            'sort_order' => 'integer',
        ]);

        if($request->parent_id == $footerMenu->id) {
            return back()->with('error', 'A menu cannot be its own parent.');
        }

        $footerMenu->update([
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.footer-setup.index')->with('success', 'Footer menu updated successfully.');
    }

    public function destroyMenu(FooterMenu $footerMenu)
    {
        $footerMenu->delete();
        return redirect()->route('admin.footer-setup.index')->with('success', 'Footer menu deleted successfully.');
    }

    public function storeGeneralLink(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'integer',
        ]);

        GeneralLink::create([
            'title' => $request->title,
            'url' => $request->url,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', 'General Link created successfully.');
    }

    public function updateGeneralLink(Request $request, GeneralLink $generalLink)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'integer',
        ]);

        $generalLink->update([
            'title' => $request->title,
            'url' => $request->url,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', 'General Link updated successfully.');
    }

    public function destroyGeneralLink(GeneralLink $generalLink)
    {
        $generalLink->delete();
        return back()->with('success', 'General Link deleted successfully.');
    }
}
