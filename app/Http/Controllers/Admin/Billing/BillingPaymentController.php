<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingPayment;
use Illuminate\Http\Request;

class BillingPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientId = $request->input('client_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch all organizations/clients for filter dropdown
        $organisations = \App\Models\Organisation::orderBy('name')->get();

        // 1. Pending Invoices (Unpaid or Partially Paid Invoices)
        $invoicesQuery = \App\Models\BillingInvoice::with(['organisation', 'payments'])
            ->whereIn('status', ['unpaid', 'partial']);

        if ($clientId) {
            $invoicesQuery->where('organisation_id', $clientId);
        }

        if ($startDate) {
            $invoicesQuery->whereDate('issue_date', '>=', $startDate);
        }

        if ($endDate) {
            $invoicesQuery->whereDate('issue_date', '<=', $endDate);
        }

        $pendingInvoices = $invoicesQuery->latest()->get();

        // 2. Payment History
        $paymentsQuery = BillingPayment::with('invoice.organisation');

        if ($clientId) {
            $paymentsQuery->whereHas('invoice', function ($q) use ($clientId) {
                $q->where('organisation_id', $clientId);
            });
        }

        if ($startDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $paymentsQuery->latest()->paginate(15)->withQueryString();

        return view('admin.billing.payments.index', compact('payments', 'pendingInvoices', 'organisations', 'clientId', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $invoices = \App\Models\BillingInvoice::whereIn('status', ['unpaid', 'partial'])->get();
        $selectedInvoiceId = $request->query('invoice_id');
        
        return view('admin.billing.payments.create', compact('invoices', 'selectedInvoiceId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'billing_invoice_id' => 'required|exists:billing_invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:bank_transfer,upi,cash,cheque,other,tds',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = \App\Models\BillingInvoice::findOrFail($request->billing_invoice_id);
        
        // Calculate remaining amount
        $paidSoFar = $invoice->payments()->sum('amount');
        $remaining = $invoice->total_amount - $paidSoFar;

        if ($request->amount > $remaining) {
            return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds the remaining invoice balance (₹' . number_format($remaining, 2) . ').']);
        }

        // Map the snake_case input to Enum
        $paymentModes = [
            'bank_transfer' => 'Bank Transfer',
            'upi' => 'UPI',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'other' => 'Bank Transfer',
            'tds' => 'TDS'
        ];
        
        $payment = BillingPayment::create([
            'invoice_id' => $invoice->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_mode' => $paymentModes[$request->payment_method] ?? 'Bank Transfer',
            'transaction_id' => $request->transaction_id,
            'notes' => $request->notes,
        ]);

        // Update Invoice status
        $newPaidSoFar = $paidSoFar + $request->amount;
        if ($newPaidSoFar >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('admin.billing.invoices.show', $invoice->id)->with('success', 'Payment recorded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = BillingPayment::with('invoice.organisation')->findOrFail($id);
        return view('admin.billing.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payment = BillingPayment::with('invoice.organisation')->findOrFail($id);
        return view('admin.billing.payments.edit', compact('payment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payment = BillingPayment::with('invoice')->findOrFail($id);
        $invoice = $payment->invoice;

        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|in:bank_transfer,upi,cash,cheque,tds,other',
            'transaction_id' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        // Paid so far EXCLUDING this payment
        $paidExcludingThis = $invoice->payments()->where('id', '!=', $id)->sum('amount');
        $remaining = $invoice->total_amount - $paidExcludingThis;

        if ($request->amount > $remaining) {
            return back()->withInput()->withErrors([
                'amount' => 'Payment amount exceeds remaining invoice balance (₹' . number_format($remaining, 2) . ').'
            ]);
        }

        $paymentModes = [
            'bank_transfer' => 'Bank Transfer',
            'upi'           => 'UPI',
            'cash'          => 'Cash',
            'cheque'        => 'Cheque',
            'tds'           => 'TDS',
            'other'         => 'Bank Transfer',
        ];

        $payment->update([
            'amount'         => $request->amount,
            'payment_date'   => $request->payment_date,
            'payment_mode'   => $paymentModes[$request->payment_method] ?? 'Bank Transfer',
            'transaction_id' => $request->transaction_id,
            'notes'          => $request->notes,
        ]);

        // Recalculate invoice status
        $newPaid = $invoice->payments()->sum('amount');
        if ($newPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($newPaid > 0) {
            $invoice->update(['status' => 'partial']);
        } else {
            $invoice->update(['status' => 'unpaid']);
        }

        return redirect()->route('admin.billing.payments.index')
            ->with('success', 'Payment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = BillingPayment::findOrFail($id);
        $invoice = $payment->invoice;
        
        $payment->delete();
        
        // Recalculate invoice status
        $paidSoFar = $invoice->payments()->sum('amount');
        if ($paidSoFar == 0) {
            $invoice->update(['status' => 'unpaid']);
        } elseif ($paidSoFar < $invoice->total_amount) {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('admin.billing.payments.index')->with('success', 'Payment deleted successfully.');
    }
}
