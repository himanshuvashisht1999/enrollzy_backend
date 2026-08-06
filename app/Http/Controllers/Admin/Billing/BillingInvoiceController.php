<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use Illuminate\Http\Request;

class BillingInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientId  = $request->input('client_id');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $organisations = \App\Models\Organisation::orderBy('name')->get();

        $query = BillingInvoice::with('organisation')->latest();

        if ($clientId) {
            $query->where('organisation_id', $clientId);
        }
        if ($startDate) {
            $query->whereDate('issue_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('issue_date', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('admin.billing.invoices.index', compact('invoices', 'organisations', 'clientId', 'startDate', 'endDate', 'status'));
    }

    public function create()
    {
        $organisations = \App\Models\Organisation::all();
        $services = \App\Models\BillingService::where('status', 1)->get();
        return view('admin.billing.invoices.create', compact('organisations', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'campus_id' => 'required|exists:campuses,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:billing_services,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach($request->items as $item) {
            $row_total = $item['quantity'] * $item['unit_price'];
            $subtotal += $row_total;
        }

        $discount = $request->discount_amount ?: 0;
        
        // Take tax amounts directly from the form so admin can override them
        $cgst = $request->cgst_amount ?: 0;
        $sgst = $request->sgst_amount ?: 0;
        $igst = $request->igst_amount ?: 0;
        $total_tax = $cgst + $sgst + $igst;
        
        $total_amount = $subtotal - $discount + $total_tax;

        // Generate Invoice Number (e.g., INV-2026-0001)
        $latestId = BillingInvoice::withTrashed()->max('id');
        $nextId = $latestId ? $latestId + 1 : 1;
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $invoice = BillingInvoice::create([
            'organisation_id' => $request->organisation_id,
            'campus_id' => $request->campus_id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_tax' => $total_tax,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'total_amount' => $total_amount,
            'status' => 'unpaid',
            'notes' => $request->notes,
        ]);

        foreach($request->items as $item) {
            $row_total = $item['quantity'] * $item['unit_price'];
            $invoice->items()->create([
                'billing_service_id' => $item['service_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $row_total,
            ]);
        }

        return redirect()->route('admin.billing.invoices.show', $invoice->id)->with('success', 'Invoice generated successfully!');
    }

    public function show(string $id)
    {
        $invoice = BillingInvoice::with(['organisation', 'campus', 'items.service', 'payments'])->findOrFail($id);
        $setting = \App\Models\Setting::first();
        return view('admin.billing.invoices.show', compact('invoice', 'setting'));
    }

    public function edit(string $id)
    {
        // Usually invoices shouldn't be fully edited after creation, but we can allow some edits
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $invoice = BillingInvoice::findOrFail($id);
        $invoice->delete();
        return redirect()->route('admin.billing.invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function downloadPdf($id)
    {
        $invoice = BillingInvoice::with(['organisation', 'campus', 'items.service', 'payments'])->findOrFail($id);
        $setting = \App\Models\Setting::first();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.billing.invoices.pdf', compact('invoice', 'setting'));
        
        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
