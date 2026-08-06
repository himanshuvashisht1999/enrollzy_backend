@extends('admin.layouts.master')

@section('title', 'Manage Payments')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Payment History</h4>
        <a href="{{ route('admin.billing.payments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Record New Payment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.billing.payments.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Client / Organisation</label>
                    <select name="client_id" class="form-select select2-filter" data-placeholder="All Clients">
                        <option value="">All Clients</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ $clientId == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-light w-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    @php
        $totalPendingAmount = 0;
        $unpaidCount = 0;
        $partialCount = 0;
        foreach ($pendingInvoices as $inv) {
            $paid = $inv->payments->sum('amount');
            $totalPendingAmount += ($inv->total_amount - $paid);
            if ($inv->status == 'unpaid') {
                $unpaidCount++;
            } else {
                $partialCount++;
            }
        }
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-soft-danger text-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted fw-medium">Pending Dues Amount</p>
                            <h3 class="mb-0 fw-bold">₹{{ number_format($totalPendingAmount, 2) }}</h3>
                        </div>
                        <div class="avatar-title bg-soft-danger rounded p-3">
                            <i class="fas fa-rupee-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-soft-warning text-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted fw-medium">Partially Paid Invoices</p>
                            <h3 class="mb-0 fw-bold">{{ $partialCount }}</h3>
                        </div>
                        <div class="avatar-title bg-soft-warning rounded p-3">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-soft-info text-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted fw-medium">Unpaid Invoices</p>
                            <h3 class="mb-0 fw-bold">{{ $unpaidCount }}</h3>
                        </div>
                        <div class="avatar-title bg-soft-info rounded p-3">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Pending Bills & Payments -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs nav-tabs-custom" id="billingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="pending-tab" data-bs-toggle="tab"
                        data-bs-target="#pending-bills" type="button" role="tab" aria-controls="pending-bills"
                        aria-selected="false">
                        Pending Bills ({{ $pendingInvoices->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold active" id="history-tab" data-bs-toggle="tab" data-bs-target="#payment-history"
                        type="button" role="tab" aria-controls="payment-history" aria-selected="true">
                        Payment History ({{ $payments->total() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="billingTabsContent">
                <!-- 1. Pending Bills Tab -->
                <div class="tab-pane fade" id="pending-bills" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Invoice #</th>
                                    <th>Client / Organisation</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Total Bill</th>
                                    <th>Paid</th>
                                    <th>Balance Due</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingInvoices as $inv)
                                    @php
                                        $paid = $inv->payments->sum('amount');
                                        $balance = $inv->total_amount - $paid;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('admin.billing.invoices.show', $inv->id) }}"
                                                class="fw-bold text-decoration-none">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $inv->organisation->name ?? 'N/A' }}</td>
                                        <td>{{ $inv->issue_date ? $inv->issue_date->format('d M, Y') : 'N/A' }}</td>
                                        <td>
                                            <span
                                                class="{{ $inv->due_date && $inv->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ $inv->due_date ? $inv->due_date->format('d M, Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                        <td class="text-success">₹{{ number_format($paid, 2) }}</td>
                                        <td class="fw-bold text-danger">₹{{ number_format($balance, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $inv->status == 'partial' ? 'bg-soft-warning text-warning' : 'bg-soft-danger text-danger' }} px-2 py-1 rounded">
                                                {{ strtoupper($inv->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.billing.payments.create', ['invoice_id' => $inv->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Record Payment
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">No pending bills found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Payment History Tab -->
                <div class="tab-pane fade show active" id="payment-history" role="tabpanel" aria-labelledby="history-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Invoice #</th>
                                    <th>Client / Organisation</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount Paid</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $payment->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.billing.invoices.show', $payment->invoice->id) }}"
                                                class="fw-bold text-decoration-none">
                                                {{ $payment->invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $payment->invoice->organisation->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                        <td>
                                            <span
                                                class="badge bg-soft-info text-info">{{ strtoupper(str_replace('_', ' ', $payment->payment_mode)) }}</span>
                                            @if($payment->transaction_id)
                                                <br><small class="text-muted">Txn: {{ $payment->transaction_id }}</small>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.billing.payments.edit', $payment->id) }}"
                                                class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.billing.payments.destroy', $payment->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this payment record? This will affect the invoice balance.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No payments recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white pt-3 pe-4">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function () {
                if ($.fn.select2) {
                    $('.select2-filter').select2({
                        allowClear: true,
                        width: '100%'
                    });
                }
            });
        </script>
    @endpush
@endsection