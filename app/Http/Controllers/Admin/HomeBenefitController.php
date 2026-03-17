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
        ]);

        HomeBenefit::create($request->all());

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
        ]);

        $homeBenefit->update($request->all());

        return redirect()->route('admin.home-benefits.index')->with('success', 'Benefit updated successfully.');
    }

    public function destroy(HomeBenefit $homeBenefit)
    {
        $homeBenefit->delete();
        return redirect()->route('admin.home-benefits.index')->with('success', 'Benefit deleted successfully.');
    }
}
