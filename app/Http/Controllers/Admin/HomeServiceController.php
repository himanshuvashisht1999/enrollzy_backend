<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeService;
use Illuminate\Http\Request;

class HomeServiceController extends Controller
{
    public function index()
    {
        $services = HomeService::orderBy('sort_order')->get();
        return view('admin.home-services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.home-services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'footer_text' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        HomeService::create($request->all());

        return redirect()->route('admin.home-services.index')->with('success', 'Service created successfully.');
    }

    public function edit(HomeService $homeService)
    {
        return view('admin.home-services.edit', compact('homeService'));
    }

    public function update(Request $request, HomeService $homeService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'footer_text' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $homeService->update($request->all());

        return redirect()->route('admin.home-services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(HomeService $homeService)
    {
        $homeService->delete();
        return redirect()->route('admin.home-services.index')->with('success', 'Service deleted successfully.');
    }
}
