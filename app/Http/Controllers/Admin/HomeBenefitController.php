<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBenefit;
use Illuminate\Http\Request;

class HomeBenefitController extends Controller
{
    public function index()
    {
        $benefits = HomeBenefit::orderBy('sort_order')->get();
        return view('admin.home-benefits.index', compact('benefits'));
    }

    public function create()
    {
        return view('admin.home-benefits.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
            'icon' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            $iconName = time() . '_' . $request->file('icon')->getClientOriginalName();
            $request->file('icon')->move(public_path('images/benefits'), $iconName);
            $data['icon'] = 'images/benefits/' . $iconName;
        }

        HomeBenefit::create($data);

        return redirect()->route('admin.home-benefits.index')->with('success', 'Benefit created successfully.');
    }

    public function edit(HomeBenefit $homeBenefit)
    {
        return view('admin.home-benefits.edit', compact('homeBenefit'));
    }

    public function update(Request $request, HomeBenefit $homeBenefit)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
            'icon' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('icon')) {
            if ($homeBenefit->icon && file_exists(public_path($homeBenefit->icon))) {
                @unlink(public_path($homeBenefit->icon));
            }
            $iconName = time() . '_' . $request->file('icon')->getClientOriginalName();
            $request->file('icon')->move(public_path('images/benefits'), $iconName);
            $data['icon'] = 'images/benefits/' . $iconName;
        }

        $homeBenefit->update($data);

        return redirect()->route('admin.home-benefits.index')->with('success', 'Benefit updated successfully.');
    }

    public function destroy(HomeBenefit $homeBenefit)
    {
        $homeBenefit->delete();
        return redirect()->route('admin.home-benefits.index')->with('success', 'Benefit deleted successfully.');
    }
}
