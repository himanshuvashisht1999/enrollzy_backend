<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/home-services'), $imageName);
            $data['image'] = 'uploads/home-services/' . $imageName;
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
            if ($homeService->image && File::exists(public_path($homeService->image))) {
                File::delete(public_path($homeService->image));
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/home-services'), $imageName);
            $data['image'] = 'uploads/home-services/' . $imageName;
        }

        $homeService->update($data);

        return redirect()->route('admin.home-services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(HomeService $homeService)
    {
        if ($homeService->image && File::exists(public_path($homeService->image))) {
            File::delete(public_path($homeService->image));
        }
        $homeService->delete();
        return redirect()->route('admin.home-services.index')->with('success', 'Service deleted successfully.');
    }
}
