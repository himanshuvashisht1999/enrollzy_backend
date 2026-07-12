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
    public function index()
    {
        $payments = BillingPayment::with('invoice.organisation')->latest()->paginate(15);
        return view('admin.billing.payments.index', compact('payments'));
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
            'payment_method' => 'required|string|in:bank_transfer,upi,cash,cheque,other',
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
            'other' => 'Bank Transfer'
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
        // Typically payments are not editable, but we'll leave stub if needed.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
