<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingService;
use Illuminate\Http\Request;

class BillingServiceController extends Controller
{
    public function index()
    {
        $services = BillingService::latest()->paginate(15);
        return view('admin.billing.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.billing.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'hsn_sac_code' => 'nullable|string|max:50',
            'status' => 'boolean',
        ]);

        BillingService::create($request->all());

        return redirect()->route('admin.billing.services.index')->with('success', 'Service created successfully.');
    }

    public function show(string $id)
    {
        // Not used for services
    }

    public function edit(string $id)
    {
        $service = BillingService::findOrFail($id);
        return view('admin.billing.services.edit', compact('service'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'hsn_sac_code' => 'nullable|string|max:50',
            'status' => 'boolean',
        ]);

        $service = BillingService::findOrFail($id);
        $service->update($request->all());

        return redirect()->route('admin.billing.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(string $id)
    {
        $service = BillingService::findOrFail($id);
        $service->delete();
        return redirect()->route('admin.billing.services.index')->with('success', 'Service deleted successfully.');
    }
}
