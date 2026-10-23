@extends('admin.layouts.master')

@section('title', 'Vendor Payables Ageing')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <h3 class="fw-bold mb-1"><i class="fas fa-truck-loading text-secondary me-2"></i>Vendor Payables Ageing Report</h3>
            <p class="text-muted small mb-0">Ageing Analysis of Outstanding Supplier Bills as of {{ \Carbon\Carbon::parse($asOfDate)->format('d F Y') }}</p>
        </div>
        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.reports.vendor_ageing') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Ageing As Of Date</label>
                    <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Update Ageing</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ageing Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Vendor Name</th>
                            <th class="text-end" style="width: 140px;">Current (&le;0 Days)</th>
                            <th class="text-end" style="width: 140px;">1 - 30 Days</th>
                            <th class="text-end" style="width: 140px;">31 - 60 Days</th>
                            <th class="text-end" style="width: 140px;">61 - 90 Days</th>
                            <th class="text-end" style="width: 140px;">90+ Days</th>
                            <th class="text-end" style="width: 160px;">Total Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['parties'] as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.accounting.parties.show', $p['party_id']) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $p['party_name'] }}
                                    </a>
                                </td>
                                <td class="text-end text-muted">{{ $p['current'] > 0 ? '₹' . number_format($p['current'], 2) : '—' }}</td>
                                <td class="text-end {{ $p['age_1_30'] > 0 ? 'fw-bold text-warning' : 'text-muted' }}">{{ $p['age_1_30'] > 0 ? '₹' . number_format($p['age_1_30'], 2) : '—' }}</td>
                                <td class="text-end {{ $p['age_31_60'] > 0 ? 'fw-bold text-danger' : 'text-muted' }}">{{ $p['age_31_60'] > 0 ? '₹' . number_format($p['age_31_60'], 2) : '—' }}</td>
                                <td class="text-end {{ $p['age_61_90'] > 0 ? 'fw-bold text-danger' : 'text-muted' }}">{{ $p['age_61_90'] > 0 ? '₹' . number_format($p['age_61_90'], 2) : '—' }}</td>
                                <td class="text-end {{ $p['age_90_plus'] > 0 ? 'fw-bold text-danger bg-danger-subtle' : 'text-muted' }}">{{ $p['age_90_plus'] > 0 ? '₹' . number_format($p['age_90_plus'], 2) : '—' }}</td>
                                <td class="text-end fw-bold fs-6 text-danger">₹{{ number_format($p['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No outstanding payables found as of this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold fs-6">
                        <tr>
                            <td class="text-uppercase">Total Outstanding:</td>
                            <td class="text-end">₹{{ number_format($report['totals']['current'], 2) }}</td>
                            <td class="text-end text-warning">₹{{ number_format($report['totals']['age_1_30'], 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($report['totals']['age_31_60'], 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($report['totals']['age_61_90'], 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($report['totals']['age_90_plus'], 2) }}</td>
                            <td class="text-end text-danger fs-5">₹{{ number_format($report['totals']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
