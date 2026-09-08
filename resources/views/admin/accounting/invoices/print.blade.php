<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; background: #fff; }
        .invoice-box { max-width: 900px; margin: auto; padding: 30px; border: 1px solid #eee; }
        @media print {
            .no-print { display: none; }
            .invoice-box { border: 0; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print p-3 text-end bg-light border-bottom mb-4">
        <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>

    <div class="invoice-box">
        <div class="row align-items-center mb-4">
            <div class="col-6">
                <h2 class="fw-bold text-primary mb-1">{{ config('app.name', 'Enrollzy') }}</h2>
                <p class="small text-muted mb-0">Enrollzy Global Solutions Pvt Ltd<br>New Delhi, India &bull; GSTIN: 07AABCE1234F1Z1</p>
            </div>
            <div class="col-6 text-end">
                <h3 class="fw-bold text-dark mb-0">TAX INVOICE</h3>
                <span class="badge bg-secondary font-monospace">{{ $invoice->invoice_number }}</span>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase small text-muted">Billed To:</h6>
                <h5 class="fw-bold mb-1">{{ $invoice->party->name }}</h5>
                @if($invoice->party->legal_name && $invoice->party->legal_name !== $invoice->party->name)
                    <div class="text-muted small">{{ $invoice->party->legal_name }}</div>
                @endif
                <div class="small text-muted mt-2">
                    @if($invoice->party->gstin) <div><strong>GSTIN:</strong> {{ $invoice->party->gstin }}</div> @endif
                    @if($invoice->party->pan) <div><strong>PAN:</strong> {{ $invoice->party->pan }}</div> @endif
                    @if($invoice->party->billing_address) <div>{{ $invoice->party->billing_address }}</div> @endif
                    @if($invoice->party->state) <div>State: {{ $invoice->party->state }} ({{ $invoice->party->state_code ?? '—' }})</div> @endif
                </div>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase small text-muted">Details:</h6>
                <div class="small">
                    <div><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</div>
                    <div><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</div>
                    <div><strong>Place of Supply:</strong> {{ $invoice->place_of_supply ?: 'Delhi' }}</div>
                </div>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>#</th>
                    <th>Item & Description</th>
                    <th>HSN/SAC</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Taxable</th>
                    <th class="text-center">GST</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->item_name }}</div>
                            @if($item->description) <small class="text-muted">{{ $item->description }}</small> @endif
                        </td>
                        <td>{{ $item->hsn_sac ?: '—' }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">₹{{ number_format($item->taxable_amount, 2) }}</td>
                        <td class="text-center">{{ $item->gst_rate }}%</td>
                        <td class="text-end fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end mb-4">
            <div class="col-5">
                <table class="table table-sm table-borderless text-end">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td>₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->cgst_amount > 0)
                    <tr>
                        <td>CGST:</td>
                        <td>₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->sgst_amount > 0)
                    <tr>
                        <td>SGST:</td>
                        <td>₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->igst_amount > 0)
                    <tr>
                        <td>IGST:</td>
                        <td>₹{{ number_format($invoice->igst_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td class="fw-bold fs-5">Total:</td>
                        <td class="fw-bold fs-5 text-success">₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($invoice->terms)
            <div class="border-top pt-3 small text-muted">
                <strong>Terms & Conditions:</strong> {{ $invoice->terms }}
            </div>
        @endif
    </div>
</body>
</html>
