@extends('admin.layouts.master')

@section('title', 'Manage Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Billing Invoices</h4>
    <a href="{{ route('admin.billing.invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Invoice
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
                        <th class="ps-4">Invoice #</th>
                        <th>Organisation</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->organisation ? $invoice->organisation->name : 'N/A' }}</td>
                        <td>{{ $invoice->issue_date->format('d M, Y') }}</td>
                        <td>{{ $invoice->due_date->format('d M, Y') }}</td>
                        <td class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @if($invoice->status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($invoice->status == 'partial')
                                <span class="badge bg-warning">Partial</span>
                            @elseif($invoice->status == 'unpaid')
                                <span class="badge bg-danger">Unpaid</span>
                            @elseif($invoice->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.billing.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <form action="{{ route('admin.billing.invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this invoice?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
