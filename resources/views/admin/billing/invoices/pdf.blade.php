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
        .header {
            width: 100%;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .company-details {
            text-align: left;
            vertical-align: top;
        }
        .invoice-details {
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #0056b3;
            margin: 0;
            margin-bottom: 3px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-data {
            font-size: 10px;
            color: #555;
            line-height: 1.4;
        }
        .meta-data strong {
            color: #333;
        }
        .billing-section {
            width: 100%;
            margin-bottom: 15px;
        }
        .billing-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        .billing-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background: #0056b3;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .totals-section {
            width: 100%;
        }
        .totals-table {
            width: 35%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f1f3f5;
        }
        .totals-table td.label {
            font-weight: bold;
            color: #555;
        }
        .totals-table td.amount {
            text-align: right;
        }
        .totals-table tr.grand-total td {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            background: #eef5fb;
            border-top: 1px solid #0056b3;
            border-bottom: 1px solid #0056b3;
        }
        .totals-table tr.balance-due td {
            font-size: 12px;
            font-weight: bold;
            color: #dc3545;
        }
        .footer {
            clear: both;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 9px;
            color: #888;
        }
        .notes-section {
            clear: both;
            margin-top: 15px;
            background: #fdfdfd;
            padding: 8px;
            border-left: 3px solid #0056b3;
            font-size: 10px;
        }
        .status-stamp {
            position: absolute;
            top: 150px;
            right: 50px;
            font-size: 30px;
            font-weight: bold;
            text-transform: uppercase;
            border: 3px solid;
            padding: 5px 10px;
            border-radius: 6px;
            transform: rotate(-15deg);
            opacity: 0.15;
            z-index: -1;
        }
        .status-paid { color: #198754; border-color: #198754; }
        .status-unpaid { color: #dc3545; border-color: #dc3545; }
    </style>
</head>
<body>
    <div class="invoice-container">
        
        @if($invoice->total_amount <= $invoice->payments->sum('amount') && $invoice->total_amount > 0)
            <div class="status-stamp status-paid">PAID</div>
        @else
            <div class="status-stamp status-unpaid">UNPAID</div>
        @endif

        <div class="header">
            <table>
                <tr>
                    <td class="company-details">
                        <h1 class="company-name">{{ $setting->site_name ?? 'Enrollzy' }}</h1>
                        <div class="meta-data">
                            @if($setting && $setting->address)
                                {!! nl2br(e($setting->address)) !!}<br>
                            @endif
                            @if($setting && $setting->contact_phone)
                                Phone: {{ $setting->contact_phone }}<br>
                            @endif
                            @if($setting && $setting->contact_email)
                                Email: {{ $setting->contact_email }}<br>
                            @endif
                        </div>
                    </td>
                    <td class="invoice-details">
                        <h1 class="invoice-title">INVOICE</h1>
                        <div class="meta-data mt-10">
                            <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
                            <strong>Issue Date:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}<br>
                            <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="billing-section" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" style="padding-right: 20px; vertical-align: top;">
                    <div class="billing-box">
                        <div class="billing-title">Billed To</div>
                        <div class="meta-data">
                            <strong style="font-size: 14px; color: #000;">{{ $invoice->organisation->name ?? 'N/A' }}</strong><br>
                            @if($invoice->organisation && $invoice->organisation->address)
                                {!! nl2br(e($invoice->organisation->address)) !!}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->email)
                                Email: {{ $invoice->organisation->email }}<br>
                            @endif
                            @if($invoice->organisation && $invoice->organisation->phone)
                                Phone: {{ $invoice->organisation->phone }}
                            @endif
                        </div>
                    </div>
                </td>
                <td width="50%" style="padding-left: 20px; vertical-align: top;">
                    <div class="billing-box">
                        <div class="billing-title">Payment Information</div>
                        <div class="meta-data">
                            Please make payments payable to <strong>{{ $setting->site_name ?? 'Enrollzy' }}</strong>.<br>
                            Ensure the invoice number is included in your payment reference.
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item / Service</th>
                    <th>Description</th>
                    <th class="center" width="10%">Qty</th>
                    <th class="right" width="15%">Price</th>
                    <th class="right" width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td><strong>{{ $item->service->name ?? 'Custom Service' }}</strong></td>
                    <td>{{ $item->description }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="amount">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="label">Discount</td>
                    <td class="amount text-danger">-{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->cgst_amount > 0)
                <tr>
                    <td class="label">CGST</td>
                    <td class="amount">{{ number_format($invoice->cgst_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->sgst_amount > 0)
                <tr>
                    <td class="label">SGST</td>
                    <td class="amount">{{ number_format($invoice->sgst_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->igst_amount > 0)
                <tr>
                    <td class="label">IGST</td>
                    <td class="amount">{{ number_format($invoice->igst_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label">Grand Total</td>
                    <td class="amount">₹{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label" style="font-weight: normal; color: #888;">Amount Paid</td>
                    <td class="amount" style="color: #198754;">₹{{ number_format($invoice->payments->sum('amount'), 2) }}</td>
                </tr>
                <tr class="balance-due">
                    <td class="label">Balance Due</td>
                    <td class="amount">₹{{ number_format(max(0, $invoice->total_amount - $invoice->payments->sum('amount')), 2) }}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        @if($invoice->notes)
        <div class="notes-section">
            <strong style="color: #0056b3;">Notes & Terms:</strong><br>
            <span class="meta-data">{{ $invoice->notes }}</span>
        </div>
        @endif

        <div class="footer">
            Generated by <strong>{{ $setting->site_name ?? 'Enrollzy' }}</strong> on {{ now()->format('F d, Y \a\t H:i') }}.<br>
            @if($setting && $setting->contact_email)
                {{ $setting->contact_email }} | 
            @endif
            @if($setting && $setting->contact_phone)
                {{ $setting->contact_phone }}
            @endif
        </div>

    </div>
</body>
</html>
