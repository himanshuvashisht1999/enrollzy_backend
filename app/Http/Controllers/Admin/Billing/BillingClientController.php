<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingClient;
use Illuminate\Http\Request;

class BillingClientController extends Controller
{
    public function index(Request $request)
    {
        $query = BillingClient::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('tan_number', 'like', "%{$search}%")
                  ->orWhere('pan_number', 'like', "%{$search}%")
                  ->orWhere('cin_number', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($companyType = $request->input('company_type')) {
            $query->where('company_type', $companyType);
        }

        if ($request->filled('status')) {
            $query->where('status', (bool) $request->input('status'));
        }

        $clients = $query->latest()->paginate(15)->withQueryString();
        $companyTypes = BillingClient::companyTypes();

        return view('admin.billing.clients.index', compact('clients', 'companyTypes'));
    }

    public function create()
    {
        $companyTypes = BillingClient::companyTypes();
        return view('admin.billing.clients.create', compact('companyTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gstin' => 'nullable|string|max:25',
            'tan_number' => 'nullable|string|max:25',
            'pan_number' => 'nullable|string|max:25',
            'cin_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = $request->has('status') ? (bool) $request->input('status') : true;

        BillingClient::create($validated);

        return redirect()->route('admin.billing.clients.index')->with('success', 'Billing Client created successfully.');
    }

    public function show(string $id)
    {
        $client = BillingClient::findOrFail($id);
        return view('admin.billing.clients.show', compact('client'));
    }

    public function edit(string $id)
    {
        $client = BillingClient::findOrFail($id);
        $companyTypes = BillingClient::companyTypes();
        return view('admin.billing.clients.edit', compact('client', 'companyTypes'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gstin' => 'nullable|string|max:25',
            'tan_number' => 'nullable|string|max:25',
            'pan_number' => 'nullable|string|max:25',
            'cin_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = $request->has('status') ? (bool) $request->input('status') : false;

        $client = BillingClient::findOrFail($id);
        $client->update($validated);

        return redirect()->route('admin.billing.clients.index')->with('success', 'Billing Client updated successfully.');
    }

    public function destroy(string $id)
    {
        $client = BillingClient::findOrFail($id);
        $client->delete();

        return redirect()->route('admin.billing.clients.index')->with('success', 'Billing Client deleted successfully.');
    }
}
