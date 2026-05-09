@extends('admin.layouts.master')

@section('title', 'Advance Pay & Penalties')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Transaction History</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.hr.advance.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> New Transaction
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.advance.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Employee</label>
                    <select name="staff_id" class="form-select rounded-3">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('staff_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">From</label>
                    <input type="date" name="from" class="form-control rounded-3" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">To</label>
                    <input type="date" name="to" class="form-control rounded-3" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Filter</button>
                    <a href="{{ route('admin.hr.advance.index') }}" class="btn btn-light rounded-pill border"><i class="fas fa-sync"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="advanceTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($advance as $row)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ date('d M, Y', strtotime($row->initiation_date)) }}</div>
                                <small class="text-muted">{{ date('h:i A', strtotime($row->created_at)) }}</small>
                            </td>
                            <td>{{ $row->employee->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $row->transaction_for == 'advance' ? 'bg-primary-soft text-primary' : 'bg-danger-soft text-danger' }} rounded-pill px-3">
                                    {{ ucfirst($row->transaction_for) }}
                                </span>
                            </td>
                            <td class="fw-bold fs-6">
                                @if($row->debit > 0)
                                    <span class="text-danger">+ {{ env('CURRENCY', '₹') }} {{ number_format($row->debit, 2) }}</span>
                                @else
                                    <span class="text-success">- {{ env('CURRENCY', '₹') }} {{ number_format($row->credit, 2) }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ env('CURRENCY', '₹') }} {{ number_format($row->balance, 2) }}</td>
                            <td><span class="badge bg-success rounded-pill px-2">Cleared</span></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $row->payment_method)) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#advanceTable').DataTable({
            order: [[0, 'desc']],
            language: { search: "_INPUT_", searchPlaceholder: "Search transactions..." }
        });
    });
</script>
@endpush
