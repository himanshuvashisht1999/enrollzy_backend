@extends('admin.layouts.master')

@section('title', 'Manage Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Billing Invoices</h4>
        <p class="text-muted small mb-0">Track and manage invoices for Organisations and Clients.</p>
    </div>
    <a href="{{ route('admin.billing.invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Create New Invoice
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.billing.invoices.index') }}" class="row g-3 align-items-end">

            {{-- Recipient Type --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Billed To Type</label>
                <select name="recipient_type" class="form-select" id="recipientTypeFilter">
                    <option value="">All Types</option>
                    <option value="organisation" {{ request('recipient_type') === 'organisation' ? 'selected' : '' }}>Organisations</option>
                    <option value="client" {{ request('recipient_type') === 'client' ? 'selected' : '' }}>Clients</option>
                </select>
            </div>

            {{-- Client / Organisation Filter --}}
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Recipient Name</label>
                <div id="orgFilterBox" class="{{ request('recipient_type') === 'client' ? 'd-none' : '' }}">
                    <select name="organisation_id" class="form-select select2-filter" data-placeholder="Select Organisation">
                        <option value="">All Organisations</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="clientFilterBox" class="{{ request('recipient_type') === 'client' ? '' : 'd-none' }}">
                    <select name="client_id" class="form-select select2-filter" data-placeholder="Select Client">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->company_type ? '(' . $c->company_type . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- From Date --}}
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-bold text-muted">From Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>

            {{-- To Date --}}
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-bold text-muted">To Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>

            {{-- Status --}}
            <div class="col-md-1">
                <label class="form-label small fw-bold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="unpaid"    {{ request('status') == 'unpaid'    ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial"   {{ request('status') == 'partial'   ? 'selected' : '' }}>Partial</option>
                    <option value="paid"      {{ request('status') == 'paid'      ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['recipient_type', 'organisation_id', 'client_id', 'start_date', 'end_date', 'status']))
                    <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Invoice #</th>
                        <th>Type</th>
                        <th>Billed To</th>
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
                        <td class="ps-4">
                            <a href="{{ route('admin.billing.invoices.show', $invoice->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>
                            @if($invoice->client_type === 'client')
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-user-tie me-1"></i> Client</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fas fa-university me-1"></i> Organisation</span>
                            @endif
                        </td>
                        <td>
                            @if($invoice->client_type === 'client' && $invoice->client)
                                <div>
                                    <span class="fw-bold text-dark">{{ $invoice->client->name }}</span>
                                    @if($invoice->client->company_type)
                                        <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">{{ $invoice->client->company_type }}</span>
                                    @endif
                                </div>
                                @if($invoice->client->gstin)
                                    <div class="small text-muted font-monospace" style="font-size: 11px;">GST: {{ $invoice->client->gstin }}</div>
                                @endif
                            @elseif($invoice->organisation)
                                <div>
                                    <span class="fw-bold text-dark">{{ $invoice->organisation->name }}</span>
                                    @if($invoice->campus)
                                        <div class="small text-muted">{{ $invoice->campus->campus_name }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $invoice->issue_date ? $invoice->issue_date->format('d M, Y') : '—' }}</td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d M, Y') : '—' }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @if($invoice->status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($invoice->status == 'partial')
                                <span class="badge bg-warning text-dark">Partial</span>
                            @elseif($invoice->status == 'unpaid')
                                <span class="badge bg-danger">Unpaid</span>
                            @elseif($invoice->status == 'cancelled')
                                <span class="badge bg-secondary">Cancelled</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.billing.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary" title="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.billing.invoices.edit', $invoice->id) }}" class="btn btn-outline-info" title="Edit Invoice">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.billing.invoices.pdf', $invoice->id) }}" class="btn btn-outline-danger" title="Download PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @if($invoice->status === 'unpaid')
                                <form action="{{ route('admin.billing.invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-dark" title="Cancel Invoice" onclick="return confirm('Are you sure you want to cancel invoice {{ $invoice->invoice_number }}?')">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            <h6 class="text-muted">No invoices found</h6>
                            <p class="small text-muted mb-3">Create your first invoice for an organisation or client.</p>
                            <a href="{{ route('admin.billing.invoices.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Create Invoice
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer bg-white pt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices</div>
            <div>{{ $invoices->links() }}</div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('js')
<script>
    $(document).ready(function () {
        $('#recipientTypeFilter').on('change', function() {
            const val = $(this).val();
            if (val === 'client') {
                $('#orgFilterBox').addClass('d-none');
                $('#clientFilterBox').removeClass('d-none');
            } else if (val === 'organisation') {
                $('#clientFilterBox').addClass('d-none');
                $('#orgFilterBox').removeClass('d-none');
            } else {
                $('#clientFilterBox').addClass('d-none');
                $('#orgFilterBox').removeClass('d-none');
            }
        });
    });
</script>
@endpush
