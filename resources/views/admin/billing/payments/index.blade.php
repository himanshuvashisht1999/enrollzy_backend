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
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Invoice #</th>
                        <th>Organisation</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="ps-4 text-muted">{{ $payment->id }}</td>
                        <td>
                            <a href="{{ route('admin.billing.invoices.show', $payment->invoice->id) }}" class="fw-bold text-decoration-none">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $payment->invoice->organisation->name ?? 'N/A' }}</td>
                        <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_', ' ', $payment->payment_mode)) }}</span>
                            @if($payment->transaction_id)
                                <br><small class="text-muted">Txn: {{ $payment->transaction_id }}</small>
                            @endif
                        </td>
                        <td class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.billing.payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this payment record? This will affect the invoice balance.')">
                                    <i class="fas fa-trash"></i>
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
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $payments->links() }}
    </div>
</div>
@endsection
