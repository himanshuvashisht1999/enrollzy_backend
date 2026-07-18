<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333333;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        
        .card {
            border-top: 3px solid #0056b3;
            padding: 20px;
            position: relative;
        }

        .status-stamp {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid;
            padding: 5px 15px;
            border-radius: 4px;
            transform: rotate(-15deg);
            opacity: 0.15;
            z-index: -1;
        }
        .status-paid { color: #198754; border-color: #198754; }
        .status-unpaid { color: #dc3545; border-color: #dc3545; }

        .header-table {
            width: 100%;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            width: 33.33%;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0 0 5px 0;
        }
        .text-muted {
            color: #6c757d;
        }
        .text-dark {
            color: #212529;
        }
        .fw-bold {
            font-weight: bold;
        }

        .billed-to-title {
            text-transform: uppercase;
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 10px;
        }

        .invoice-title {
            text-transform: uppercase;
            font-weight: 900;
            color: #212529;
            font-size: 24px;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
            text-align: right;
        }
        
        .invoice-meta {
            width: auto;
            float: right;
            border-collapse: collapse;
        }
        .invoice-meta td {
            padding: 2px 0 2px 10px;
        }
        .invoice-meta th {
            text-align: right;
            color: #6c757d;
            font-weight: normal;
            padding: 2px 10px 2px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .items-table thead th {
            background-color: #0056b3;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px;
            text-align: left;
            border: 1px solid #0056b3;
        }
        .items-table tbody td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .items-table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: top;
        }

        .info-box {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
        }
        .info-box-secondary {
            border-left: 3px solid #6c757d;
        }
        .info-box-primary {
            border-left: 3px solid #0d6efd;
        }

        .info-box h6 {
            font-weight: bold;
            margin: 0 0 5px 0;
            font-size: 11px;
        }
        .info-box-secondary h6 { color: #6c757d; }
        .info-box-primary h6 { color: #0d6efd; }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            line-height: 1.1;
        }
        .totals-table td {
            padding: 6px;
        }
        
        .grand-total-row td {
            background-color: #eef5fb;
            border-top: 1px solid #0056b3;
            border-bottom: 1px solid #0056b3;
            font-weight: bold;
            color: #212529;
            font-size: 13px;
        }
        
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }

    </style>
</head>
<body>
    
    <div class="card">


        <table class="header-table">
            <tr>
                <!-- Company Details -->
                <td style="padding-right: 10px;">
                    <div class="company-name">{{ $setting->site_name ?? 'Enrollzy' }}</div>
                    <div class="text-muted" style="line-height: 1.3;">
                        @if($setting && $setting->address)
                            {!! nl2br(e($setting->address)) !!}<br>
                        @endif
                        @if($setting && $setting->contact_phone)
                            <strong class="text-dark">Phone:</strong> {{ $setting->contact_phone }}<br>
                        @endif
                        @if($setting && $setting->contact_email)
                            <strong class="text-dark">Email:</strong> {{ $setting->contact_email }}
                        @endif
                    </div>
                </td>
                
                <!-- Billed To -->
                <td style="padding: 0 10px;">
                    <div class="billed-to-title">Billed To</div>
                    @if($invoice->campus)
                        <div class="fw-bold text-dark" style="font-size: 13px; margin-bottom: 3px;">{{ $invoice->organisation->name ?? '' }} - {{ $invoice->campus->campus_name }}</div>
                        <div class="text-muted" style="line-height: 1.3;">
                            @if($invoice->campus->full_address)
                                {!! nl2br(e($invoice->campus->full_address)) !!}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->email)
                                <strong class="text-dark">Email:</strong> {{ $invoice->organisation->email }}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->phone)
                                <strong class="text-dark">Phone:</strong> {{ $invoice->organisation->phone }}
                            @endif
                        </div>
                    @else
                        <div class="fw-bold text-dark" style="font-size: 13px; margin-bottom: 3px;">{{ $invoice->organisation->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="line-height: 1.3;">
                            @if($invoice->organisation && $invoice->organisation->address)
                                {!! nl2br(e($invoice->organisation->address)) !!}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->email)
                                <strong class="text-dark">Email:</strong> {{ $invoice->organisation->email }}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->phone)
                                <strong class="text-dark">Phone:</strong> {{ $invoice->organisation->phone }}
                            @endif
                        </div>
                    @endif
                </td>

                <!-- Invoice Details -->
                <td style="padding-left: 10px;">
                    <div class="invoice-title">Invoice</div>
                    <table class="invoice-meta">
                        <tr>
                            <th>Invoice No:</th>
                            <td class="fw-bold text-dark">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Issue Date:</th>
                            <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}</td>
                        </tr>
                        {{-- 
                        <tr>
                            <th>Due Date:</th>
                            <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}</td>
                        </tr>
                        --}}
                    </table>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item / Service</th>
                    <th>Description</th>
                    <th class="text-center" width="10%">Qty</th>
                    <th class="text-end" width="15%">Price</th>
                    <th class="text-end" width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td class="fw-bold text-dark">{{ $item->service->name ?? 'Custom Service' }}</td>
                    <td class="text-muted">{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end text-dark fw-bold">₹{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Info -->
        <table class="footer-table">
            <tr>
                <td style="width: 55%; padding-right: 20px;">
                    <div class="info-box info-box-secondary">
                        <h6>Account Details:</h6>
                        <div class="text-muted" style="line-height: 1.3;">
                            <strong class="text-dark">Bank:</strong> Bank of Baroda<br>
                            <strong class="text-dark">A/C Name:</strong> UNIBAND8 EDUCATION TECHNOLOGY PVT LTD<br>
                            <strong class="text-dark">A/C No.:</strong> 76730200001840<br>
                            <strong class="text-dark">IFSC Code:</strong> BARB0VJSCHA<br>
                            <strong class="text-dark">Branch:</strong> Chandigarh-Sector-34A
                        </div>
                    </div>

                </td>
                <td style="width: 45%;">
                    <table class="totals-table">
                        <tr>
                            <td class="text-end fw-bold text-muted w-50">Subtotal</td>
                            <td class="text-end fw-bold text-dark w-50">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <td class="text-end fw-bold text-muted">Discount</td>
                            <td class="text-end fw-bold text-danger">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        
                        {{-- GST hidden as requested
                        @if($invoice->cgst_amount > 0)
                        <tr>
                            <td class="text-end fw-bold text-muted">CGST</td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->sgst_amount > 0)
                        <tr>
                            <td class="text-end fw-bold text-muted">SGST</td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->igst_amount > 0)
                        <tr>
                            <td class="text-end fw-bold text-muted">IGST</td>
                            <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                        @endif
                        --}}

                        <tr class="grand-total-row">
                            <td class="text-end">Grand Total</td>
                            <td class="text-end">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        
                        <tr>
                            <td class="text-end text-muted pt-1 pb-0">Amount Paid</td>
                            <td class="text-end fw-bold text-success pt-1 pb-0">₹{{ number_format($invoice->payments->sum('amount'), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold text-dark py-1" style="font-size: 14px;">Balance Due</td>
                            <td class="text-end fw-bold text-danger py-1" style="font-size: 14px;">₹{{ number_format(max(0, $invoice->total_amount - $invoice->payments->sum('amount')), 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if($invoice->notes)
        <div class="info-box info-box-primary">
            <h6>Notes & Terms:</h6>
            <div class="text-muted" style="line-height: 1.3;">
                {!! nl2br(e($invoice->notes)) !!}
            </div>
        </div>
        @endif

        <div style="margin-top: 15px; font-size: 10px; color: #6c757d;">
            Generated by <strong class="text-dark">{{ $setting->site_name ?? 'Enrollzy' }}</strong> on {{ now()->format('d M Y, H:i') }}.
        </div>
    </div>

</body>
</html>
