<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Expense;
use App\Models\Accounting\Party;
use App\Models\Accounting\TaxRate;
use App\Services\Accounting\AccountingEngineService;
use App\Services\Accounting\ExpensePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ExpenseController extends Controller
{
    protected ExpensePostingService $expensePostingService;
    protected AccountingEngineService $engine;

    public function __construct(ExpensePostingService $expensePostingService, AccountingEngineService $engine)
    {
        $this->expensePostingService = $expensePostingService;
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $query = Expense::with(['categoryAccount', 'paidThroughAccount', 'party', 'submitter', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_account_id')) {
            $query->where('category_account_id', $request->category_account_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('expense_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%");
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
        $categories = ChartOfAccount::where('account_type', 'expense')->orderBy('account_code', 'asc')->get();

        return view('admin.accounting.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ChartOfAccount::where('account_type', 'expense')->orderBy('account_code', 'asc')->get();
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $parties = Party::orderBy('name', 'asc')->get();
        $taxRates = TaxRate::where('tax_type', 'gst')->where('is_active', true)->get();
        $nextExpenseNumber = $this->engine->generateNumber('EXP', 'expenses', 'expense_number');

        return view('admin.accounting.expenses.create', compact('categories', 'bankAccounts', 'parties', 'taxRates', 'nextExpenseNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'category_account_id' => 'required|exists:chart_of_accounts,id',
            'paid_through_account_id' => 'required|exists:bank_accounts,id',
            'party_id' => 'nullable|exists:parties,id',
            'subtotal' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string',
            'action' => 'required|in:save_draft,submit,pay_now',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('accounting/receipts', 'public');
        }

        $adminId = Auth::guard('admin')->id() ?? 1;
        $expenseNumber = $this->engine->generateNumber('EXP', 'expenses', 'expense_number');

        $subtotal = (float) $validated['subtotal'];
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $totalAmount = $subtotal + $taxAmount;

        $status = 'draft';
        if ($validated['action'] === 'submit') {
            $status = 'submitted';
        }

        $expense = Expense::create([
            'expense_number' => $expenseNumber,
            'expense_date' => $validated['expense_date'],
            'title' => $validated['title'],
            'category_account_id' => $validated['category_account_id'],
            'paid_through_account_id' => $validated['paid_through_account_id'],
            'party_id' => $validated['party_id'] ?? null,
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'receipt_attachment' => $receiptPath,
            'status' => $status,
            'submitted_by' => in_array($validated['action'], ['submit', 'pay_now']) ? $adminId : null,
            'submitted_at' => in_array($validated['action'], ['submit', 'pay_now']) ? now() : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($validated['action'] === 'pay_now') {
            try {
                $this->expensePostingService->approveExpense($expense);
                $this->expensePostingService->postExpense($expense);
            } catch (Exception $e) {
                return redirect()->route('admin.accounting.expenses.show', $expense->id)
                    ->with('error', 'Expense created but failed to post: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.accounting.expenses.show', $expense->id)
            ->with('success', 'Expense record created successfully.');
    }

    public function show($id)
    {
        $expense = Expense::with(['categoryAccount', 'paidThroughAccount', 'party', 'submitter', 'approver', 'poster', 'transaction.journalEntries.lines.account'])
            ->findOrFail($id);

        return view('admin.accounting.expenses.show', compact('expense'));
    }

    public function submit($id)
    {
        $expense = Expense::findOrFail($id);
        $this->expensePostingService->submitExpense($expense);

        return redirect()->back()->with('success', 'Expense submitted for approval.');
    }

    public function approve($id)
    {
        $expense = Expense::findOrFail($id);
        $this->expensePostingService->approveExpense($expense);

        return redirect()->back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $reason = $request->input('rejection_reason', 'Rejected by manager.');
        $this->expensePostingService->rejectExpense($expense, $reason);

        return redirect()->back()->with('success', 'Expense marked as rejected.');
    }

    public function pay($id)
    {
        $expense = Expense::findOrFail($id);

        try {
            $this->expensePostingService->postExpense($expense);
            return redirect()->back()->with('success', "Expense {$expense->expense_number} has been paid and posted to the General Ledger.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Payment posting failed: ' . $e->getMessage());
        }
    }
}
