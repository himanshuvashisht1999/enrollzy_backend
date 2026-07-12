@extends('admin.layouts.master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Invoice Details</h5>
    <div>
        <a href="{{ route('admin.billing.invoices.pdf', $invoice->id) }}" class="btn btn-sm btn-secondary me-1">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-top: 3px solid #0056b3 !important; font-size: 0.85rem;">
    <div class="card-body p-3 position-relative">
        
        @if($invoice->total_amount <= $invoice->payments->sum('amount') && $invoice->total_amount > 0)
            <div class="position-absolute" style="top: 15px; right: 15px; font-size: 20px; font-weight: bold; text-transform: uppercase; border: 2px solid #198754; color: #198754; padding: 2px 10px; border-radius: 4px; transform: rotate(-15deg); opacity: 0.2; pointer-events: none;">PAID</div>
        @else
            <div class="position-absolute" style="top: 15px; right: 15px; font-size: 20px; font-weight: bold; text-transform: uppercase; border: 2px solid #dc3545; color: #dc3545; padding: 2px 10px; border-radius: 4px; transform: rotate(-15deg); opacity: 0.2; pointer-events: none;">UNPAID</div>
        @endif

        <!-- Header -->
        <div class="row mb-2 border-bottom pb-2">
            <div class="col-6">
                <h4 class="fw-bold text-primary mb-1">{{ $setting->site_name ?? 'Enrollzy' }}</h4>
                <div class="text-muted lh-sm">
                    @if($setting && $setting->address)
                        {!! nl2br(e($setting->address)) !!}<br>
                    @endif
                    @if($setting && $setting->contact_phone)
                        <strong>Phone:</strong> {{ $setting->contact_phone }}<br>
                    @endif
                    @if($setting && $setting->contact_email)
                        <strong>Email:</strong> {{ $setting->contact_email }}
                    @endif
                </div>
            </div>
            <div class="col-6 text-end">
                <h4 class="text-uppercase fw-bolder text-dark mb-1" style="letter-spacing: 1px;">Invoice</h4>
                <table class="table table-sm table-borderless text-end ms-auto mb-0" style="width: auto; line-height: 1;">
                    <tbody>
                        <tr>
                            <th class="text-muted pe-2 py-0">Invoice No:</th>
                            <td class="fw-bold text-dark py-0">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted pe-2 py-0">Issue Date:</th>
                            <td class="fw-bold text-dark py-0">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted pe-2 py-0">Due Date:</th>
                            <td class="fw-bold text-dark py-0">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="row mb-2">
            <div class="col-6">
                <div class="bg-light p-2 rounded h-100 border">
                    <h6 class="text-uppercase text-muted border-bottom pb-1 mb-1 fw-bold" style="font-size: 10px;">Billed To</h6>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">{{ $invoice->organisation->name ?? 'N/A' }}</h6>
                    <div class="text-muted lh-sm">
                        @if($invoice->organisation && $invoice->organisation->address)
                            {!! nl2br(e($invoice->organisation->address)) !!}<br>
                        @endif
                        @if($invoice->organisation && $invoice->organisation->email)
                            <strong>Email:</strong> {{ $invoice->organisation->email }}<br>
                        @endif
                        @if($invoice->organisation && $invoice->organisation->phone)
                            <strong>Phone:</strong> {{ $invoice->organisation->phone }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="bg-light p-2 rounded h-100 border">
                    <h6 class="text-uppercase text-muted border-bottom pb-1 mb-1 fw-bold" style="font-size: 10px;">Payment Information</h6>
                    <div class="text-muted lh-sm">
                        Please make payments payable to <strong class="text-dark">{{ $setting->site_name ?? 'Enrollzy' }}</strong>.<br>
                        Ensure the invoice number is included in your payment reference.
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-2">
            <table class="table table-sm table-striped align-middle border mb-0">
                <thead style="background-color: #0056b3; color: white;">
                    <tr>
                        <th class="text-uppercase py-1" style="font-size: 10px;">Item / Service</th>
                        <th class="text-uppercase py-1" style="font-size: 10px;">Description</th>
                        <th class="text-center text-uppercase py-1" width="10%" style="font-size: 10px;">Qty</th>
                        <th class="text-end text-uppercase py-1" width="15%" style="font-size: 10px;">Price</th>
                        <th class="text-end text-uppercase py-1" width="20%" style="font-size: 10px;">Total</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($invoice->items as $item)
                    <tr>
                        <td class="fw-bold text-dark py-1">{{ $item->service->name ?? 'Custom Service' }}</td>
                        <td class="text-muted py-1">{{ $item->description }}</td>
                        <td class="text-center py-1">{{ $item->quantity }}</td>
                        <td class="text-end py-1">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end text-dark fw-bold py-1">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-2">No items found for this invoice.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col-7">
                @if($invoice->notes)
                <div class="mt-2 p-2 rounded bg-light border-start border-3 border-primary">
                    <h6 class="fw-bold text-primary mb-1" style="font-size: 11px;">Notes & Terms:</h6>
                    <p class="text-muted mb-0 lh-sm">{{ $invoice->notes }}</p>
                </div>
                @endif
                <div class="mt-3 text-muted" style="font-size: 10px;">
                    Generated by <strong class="text-dark">{{ $setting->site_name ?? 'Enrollzy' }}</strong> on {{ now()->format('d M Y, H:i') }}.
                </div>
            </div>
            <div class="col-5">
                <table class="table table-sm table-borderless text-end mb-0" style="line-height: 1.1;">
                    <tbody>
                        <tr>
                            <td class="fw-bold text-muted w-50 py-1">Subtotal</td>
                            <td class="fw-bold text-dark w-50 py-1">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <td class="fw-bold text-muted py-1">Discount</td>
                            <td class="fw-bold text-danger py-1">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->cgst_amount > 0)
                        <tr>
                            <td class="fw-bold text-muted py-1">CGST</td>
                            <td class="fw-bold text-dark py-1">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->sgst_amount > 0)
                        <tr>
                            <td class="fw-bold text-muted py-1">SGST</td>
                            <td class="fw-bold text-dark py-1">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->igst_amount > 0)
                        <tr>
                            <td class="fw-bold text-muted py-1">IGST</td>
                            <td class="fw-bold text-dark py-1">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        
                        <tr style="background-color: #eef5fb; border-top: 1px solid #0056b3; border-bottom: 1px solid #0056b3;">
                            <td class="fw-bold text-dark py-1" style="font-size: 13px;">Grand Total</td>
                            <td class="fw-bold text-dark py-1" style="font-size: 13px;">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        
                        <tr>
                            <td class="text-muted pt-1 pb-0">Amount Paid</td>
                            <td class="fw-bold text-success pt-1 pb-0">₹{{ number_format($invoice->payments->sum('amount'), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-dark py-1" style="font-size: 14px;">Balance Due</td>
                            <td class="fw-bold text-danger py-1" style="font-size: 14px;">₹{{ number_format(max(0, $invoice->total_amount - $invoice->payments->sum('amount')), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
