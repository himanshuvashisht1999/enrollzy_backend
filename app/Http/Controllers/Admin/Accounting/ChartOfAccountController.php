<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use Exception;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::with(['parent', 'children']);

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('account_code', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%");
            });
        }

        $accounts = $query->orderBy('account_code', 'asc')->get();

        // Group by account type for tree breakdown
        $groupedAccounts = [
            'asset' => $accounts->where('account_type', 'asset'),
            'liability' => $accounts->where('account_type', 'liability'),
            'equity' => $accounts->where('account_type', 'equity'),
            'revenue' => $accounts->where('account_type', 'revenue'),
            'expense' => $accounts->where('account_type', 'expense'),
            'tax' => $accounts->where('account_type', 'tax'),
        ];

        return view('admin.accounting.chart_of_accounts.index', compact('accounts', 'groupedAccounts'));
    }

    public function create()
    {
        $parentAccounts = ChartOfAccount::orderBy('account_code', 'asc')->get();
        return view('admin.accounting.chart_of_accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:chart_of_accounts,account_code',
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense,tax',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        ChartOfAccount::create([
            'account_code' => $validated['account_code'],
            'name' => $validated['name'],
            'account_type' => $validated['account_type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_system_account' => false,
            'is_active' => true,
        ]);

        return redirect()->route('admin.accounting.chart_of_accounts.index')
            ->with('success', 'Chart of Account created successfully.');
    }

    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $parentAccounts = ChartOfAccount::where('id', '!=', $id)->orderBy('account_code', 'asc')->get();
        return view('admin.accounting.chart_of_accounts.edit', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
        ];

        if (!$account->is_system_account) {
            $rules['account_code'] = "required|string|max:50|unique:chart_of_accounts,account_code,{$id}";
            $rules['account_type'] = 'required|in:asset,liability,equity,revenue,expense,tax';
        }

        $validated = $request->validate($rules);

        $account->update($validated);

        return redirect()->route('admin.accounting.chart_of_accounts.index')
            ->with('success', 'Chart of Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        if ($account->is_system_account) {
            return redirect()->back()->with('error', 'System core accounts cannot be deleted.');
        }

        if ($account->journalLines()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete an account that has posted journal transactions. You may deactivate it instead.');
        }

        if ($account->children()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete an account that has sub-accounts attached.');
        }

        $account->delete();

        return redirect()->route('admin.accounting.chart_of_accounts.index')
            ->with('success', 'Chart of Account deleted successfully.');
    }
}
