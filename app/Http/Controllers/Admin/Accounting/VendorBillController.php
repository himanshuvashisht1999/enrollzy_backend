<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Party;
use App\Models\Accounting\TaxRate;
use App\Models\Accounting\VendorBill;
use App\Models\Accounting\VendorBillItem;
use App\Services\Accounting\AccountingEngineService;
use App\Services\Accounting\BillPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class VendorBillController extends Controller
{
    protected BillPostingService $billPostingService;
    protected AccountingEngineService $engine;

    public function __construct(BillPostingService $billPostingService, AccountingEngineService $engine)
    {
        $this->billPostingService = $billPostingService;
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $query = VendorBill::with(['party', 'creator', 'poster', 'tdsRate']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('bill_number', 'like', "%{$s}%")
                  ->orWhere('vendor_bill_ref', 'like', "%{$s}%")
                  ->orWhereHas('party', function ($pq) use ($s) {
                      $pq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        $bills = $query->orderBy('bill_date', 'desc')->paginate(20);
        $parties = Party::where('is_vendor', true)->orderBy('name', 'asc')->get();

        return view('admin.accounting.bills.index', compact('bills', 'parties'));
    }

    public function create()
    {
        $parties = Party::where('is_vendor', true)->orderBy('name', 'asc')->get();
        $taxRates = TaxRate::where('tax_type', 'gst')->where('is_active', true)->get();
        $tdsRates = TaxRate::where('tax_type', 'tds')->where('is_active', true)->get();
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->orderBy('account_code', 'asc')->get();
        $nextBillNumber = $this->engine->generateNumber('BILL', 'vendor_bills', 'bill_number');

        return view('admin.accounting.bills.create', compact('parties', 'taxRates', 'tdsRates', 'expenseAccounts', 'nextBillNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => 'required|exists:parties,id',
            'vendor_bill_ref' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'tds_rate_id' => 'nullable|exists:tax_rates,id',
            'notes' => 'nullable|string',
            'action' => 'required|in:save_draft,save_and_post',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.hsn_sac' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.expense_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $bill = DB::transaction(function () use ($request, $validated) {
            $party = Party::findOrFail($validated['party_id']);
            $billNumber = $this->engine->generateNumber('BILL', 'vendor_bills', 'bill_number');
            $adminId = Auth::guard('admin')->id() ?? 1;

            $companyStateCode = config('app.company_state_code', '07');
            $vendorStateCode = $party->state_code ?? '07';
            $isInterState = ($vendorStateCode !== $companyStateCode);

            $subtotal = 0.00;
            $cgstTotal = 0.00;
            $sgstTotal = 0.00;
            $igstTotal = 0.00;
            $totalTax = 0.00;

            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $taxable = $qty * $unitPrice;

                $gstRate = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                if (!empty($item['tax_rate_id'])) {
                    $taxRate = TaxRate::find($item['tax_rate_id']);
                    if ($taxRate) {
                        $gstRate = (float) $taxRate->rate;
                        if ($isInterState) {
                            $igst = round(($taxable * $gstRate) / 100, 2);
                        } else {
                            $halfRate = $gstRate / 2;
                            $cgst = round(($taxable * $halfRate) / 100, 2);
                            $sgst = round(($taxable * $halfRate) / 100, 2);
                        }
                    }
                }

                $itemTax = $cgst + $sgst + $igst;
                $itemTotal = $taxable + $itemTax;

                $subtotal += $taxable;
                $cgstTotal += $cgst;
                $sgstTotal += $sgst;
                $igstTotal += $igst;
                $totalTax += $itemTax;

                $itemsData[] = [
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'hsn_sac' => $item['hsn_sac'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'taxable_amount' => $taxable,
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'gst_rate' => $gstRate,
                    'cgst_amount' => $cgst,
                    'sgst_amount' => $sgst,
                    'igst_amount' => $igst,
                    'total_amount' => $itemTotal,
                    'expense_account_id' => $item['expense_account_id'] ?? null,
                ];
            }

            // Calculate TDS if applicable (TDS is calculated on taxable subtotal before GST)
            $tdsRateVal = 0.00;
            $tdsAmount = 0.00;
            if (!empty($validated['tds_rate_id'])) {
                $tdsRecord = TaxRate::find($validated['tds_rate_id']);
                if ($tdsRecord) {
                    $tdsRateVal = (float) $tdsRecord->rate;
                    $tdsAmount = round(($subtotal * $tdsRateVal) / 100, 2);
                }
            }

            $totalAmount = $subtotal + $totalTax;
            $netPayable = max(0, $totalAmount - $tdsAmount);

            $vendorBill = VendorBill::create([
                'bill_number' => $billNumber,
                'vendor_bill_ref' => $validated['vendor_bill_ref'] ?? null,
                'party_id' => $party->id,
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => round($subtotal, 2),
                'cgst_amount' => round($cgstTotal, 2),
                'sgst_amount' => round($sgstTotal, 2),
                'igst_amount' => round($igstTotal, 2),
                'total_tax' => round($totalTax, 2),
                'tds_rate_id' => $validated['tds_rate_id'] ?? null,
                'tds_rate' => $tdsRateVal,
                'tds_amount' => $tdsAmount,
                'total_amount' => round($totalAmount, 2),
                'net_payable' => round($netPayable, 2),
                'paid_amount' => 0.00,
                'balance_due' => round($netPayable, 2),
                'payment_status' => 'unpaid',
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $adminId,
            ]);

            foreach ($itemsData as $itemData) {
                $vendorBill->items()->create($itemData);
            }

            if ($validated['action'] === 'save_and_post') {
                $this->billPostingService->postBill($vendorBill);
            }

            return $vendorBill;
        });

        return redirect()->route('admin.accounting.bills.show', $bill->id)
            ->with('success', 'Vendor Bill created successfully.');
    }

    public function show($id)
    {
        $bill = VendorBill::with(['party', 'items.taxRate', 'items.expenseAccount', 'tdsRate', 'transaction.journalEntries.lines.account', 'paymentAllocations.transaction'])
            ->findOrFail($id);

        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('admin.accounting.bills.show', compact('bill', 'bankAccounts'));
    }

    public function post($id)
    {
        $bill = VendorBill::findOrFail($id);

        try {
            $this->billPostingService->postBill($bill);
            return redirect()->back()->with('success', "Vendor Bill {$bill->bill_number} has been posted to the General Ledger.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', "Posting failed: " . $e->getMessage());
        }
    }

    public function recordPayment(Request $request, $id)
    {
        $bill = VendorBill::findOrFail($id);

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . $bill->balance_due,
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->billPostingService->recordBillPayment([
                'party_id' => $bill->party_id,
                'bank_account_id' => $validated['bank_account_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? "Payment for bill {$bill->bill_number}",
                'allocations' => [
                    ['bill_id' => $bill->id, 'amount' => $validated['amount']]
                ]
            ]);

            return redirect()->back()->with('success', 'Vendor payment recorded and applied to bill.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}
