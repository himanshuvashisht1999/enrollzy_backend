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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_text' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('home-services', 'public');
        }

        HomeService::create($data);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_text' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($homeService->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($homeService->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($homeService->image);
            }
            $data['image'] = $request->file('image')->store('home-services', 'public');
        }

        $homeService->update($data);

        return redirect()->route('admin.home-services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(HomeService $homeService)
    {
        $homeService->delete();
        return redirect()->route('admin.home-services.index')->with('success', 'Service deleted successfully.');
    }
}
