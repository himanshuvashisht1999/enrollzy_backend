<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Party;
use App\Models\Accounting\SalesInvoice;
use App\Models\Accounting\SalesInvoiceItem;
use App\Models\Accounting\TaxRate;
use App\Services\Accounting\AccountingEngineService;
use App\Services\Accounting\InvoicePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesInvoiceController extends Controller
{
    protected InvoicePostingService $invoicePostingService;
    protected AccountingEngineService $engine;

    public function __construct(InvoicePostingService $invoicePostingService, AccountingEngineService $engine)
    {
        $this->invoicePostingService = $invoicePostingService;
        $this->engine = $engine;
    }

    public function index(Request $request)
    {
        $query = SalesInvoice::with(['party', 'creator', 'poster']);

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
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('party', function ($pq) use ($s) {
                      $pq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);
        $parties = Party::where('is_customer', true)->orderBy('name', 'asc')->get();

        return view('admin.accounting.invoices.index', compact('invoices', 'parties'));
    }

    public function create()
    {
        $parties = Party::where('is_customer', true)->orderBy('name', 'asc')->get();
        $taxRates = TaxRate::where('tax_type', 'gst')->where('is_active', true)->get();
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->orderBy('account_code', 'asc')->get();
        $nextInvoiceNumber = $this->engine->generateNumber('INV', 'sales_invoices', 'invoice_number');

        return view('admin.accounting.invoices.create', compact('parties', 'taxRates', 'revenueAccounts', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => 'required|exists:parties,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'place_of_supply' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'action' => 'required|in:save_draft,save_and_post',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.hsn_sac' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.revenue_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $invoice = DB::transaction(function () use ($request, $validated) {
            $party = Party::findOrFail($validated['party_id']);
            $invoiceNumber = $this->engine->generateNumber('INV', 'sales_invoices', 'invoice_number');
            $adminId = Auth::guard('admin')->id() ?? 1;

            $companyStateCode = config('app.company_state_code', '07'); // Default 07 Delhi
            $customerStateCode = $party->state_code ?? '07';
            $isInterState = ($customerStateCode !== $companyStateCode);

            $subtotal = 0.00;
            $totalDiscount = 0.00;
            $cgstTotal = 0.00;
            $sgstTotal = 0.00;
            $igstTotal = 0.00;
            $totalTax = 0.00;

            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discount = (float) ($item['discount_amount'] ?? 0);
                $taxable = max(0, ($qty * $unitPrice) - $discount);

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
                $totalDiscount += $discount;
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
                    'discount_amount' => $discount,
                    'taxable_amount' => $taxable,
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'gst_rate' => $gstRate,
                    'cgst_amount' => $cgst,
                    'sgst_amount' => $sgst,
                    'igst_amount' => $igst,
                    'total_amount' => $itemTotal,
                    'revenue_account_id' => $item['revenue_account_id'] ?? null,
                ];
            }

            $grandTotal = $subtotal + $totalTax;

            $salesInvoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'party_id' => $party->id,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($totalDiscount, 2),
                'cgst_amount' => round($cgstTotal, 2),
                'sgst_amount' => round($sgstTotal, 2),
                'igst_amount' => round($igstTotal, 2),
                'total_tax' => round($totalTax, 2),
                'tds_deducted' => 0.00,
                'total_amount' => round($grandTotal, 2),
                'paid_amount' => 0.00,
                'balance_due' => round($grandTotal, 2),
                'payment_status' => 'unpaid',
                'status' => 'draft',
                'place_of_supply' => $validated['place_of_supply'] ?? $party->state,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => $adminId,
            ]);

            foreach ($itemsData as $itemData) {
                $salesInvoice->items()->create($itemData);
            }

            if ($validated['action'] === 'save_and_post') {
                $this->invoicePostingService->postInvoice($salesInvoice);
            }

            return $salesInvoice;
        });

        return redirect()->route('admin.accounting.invoices.show', $invoice->id)
            ->with('success', 'Sales Invoice created successfully.');
    }

    public function show($id)
    {
        $invoice = SalesInvoice::with(['party', 'items.taxRate', 'items.revenueAccount', 'transaction.journalEntries.lines.account', 'paymentAllocations.transaction'])
            ->findOrFail($id);

        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('admin.accounting.invoices.show', compact('invoice', 'bankAccounts'));
    }

    public function post($id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        try {
            $this->invoicePostingService->postInvoice($invoice);
            return redirect()->back()->with('success', "Invoice {$invoice->invoice_number} has been posted to the General Ledger.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', "Posting failed: " . $e->getMessage());
        }
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'tds_deducted' => 'nullable|numeric|min:0',
            'discount_allowed' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->invoicePostingService->recordPaymentReceipt([
                'party_id' => $invoice->party_id,
                'bank_account_id' => $validated['bank_account_id'],
                'amount' => $validated['amount'],
                'tds_deducted' => $validated['tds_deducted'] ?? 0,
                'discount_allowed' => $validated['discount_allowed'] ?? 0,
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? "Payment for invoice {$invoice->invoice_number}",
                'allocations' => [
                    ['invoice_id' => $invoice->id, 'amount' => $validated['amount'] + ($validated['tds_deducted'] ?? 0) + ($validated['discount_allowed'] ?? 0)]
                ]
            ]);

            return redirect()->back()->with('success', 'Payment receipt recorded and applied to invoice.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Payment receipt failed: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $invoice = SalesInvoice::with(['party', 'items.taxRate'])->findOrFail($id);
        return view('admin.accounting.invoices.print', compact('invoice'));
    }
}
