@extends('admin.layouts.master')

@section('title', 'New Journal Voucher')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.journals.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Journals
        </a>
        <h3 class="fw-bold mb-1">Create Manual Journal Voucher</h3>
        <p class="text-muted small mb-0">Post manual accounting entries, adjustments, accruals, or depreciation with strict double-entry balance validation.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.accounting.journals.store') }}" method="POST" id="journalForm">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Voucher Date <span class="text-danger">*</span></label>
                        <input type="date" name="journal_date" class="form-control" value="{{ old('journal_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label fw-bold">Narration / Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Monthly Depreciation for Computers & IT Equipment" value="{{ old('description') }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Lines Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Journal Entry Lines (Dr / Cr)</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn">
                    <i class="fas fa-plus me-1"></i> Add Entry Line
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="linesTable">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th style="width: 30%;">Account (COA) <span class="text-danger">*</span></th>
                                <th style="width: 20%;">Entity / Party (Optional)</th>
                                <th style="width: 25%;">Line Description</th>
                                <th class="text-end" style="width: 12%;">Debit (₹)</th>
                                <th class="text-end" style="width: 12%;">Credit (₹)</th>
                                <th style="width: 3%;"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                            <!-- Line 1: Debit default -->
                            <tr class="line-row">
                                <td>
                                    <select name="lines[0][account_id]" class="form-select form-select-sm" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->name }} ({{ strtoupper($acc->account_type) }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="lines[0][party_id]" class="form-select form-select-sm">
                                        <option value="">— None —</option>
                                        @foreach($parties as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="lines[0][description]" class="form-control form-control-sm" placeholder="Line remarks...">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[0][debit]" class="form-control form-control-sm text-end line-dr" value="0.00" min="0">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[0][credit]" class="form-control form-control-sm text-end line-cr" value="0.00" min="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-line p-0"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Line 2: Credit default -->
                            <tr class="line-row">
                                <td>
                                    <select name="lines[1][account_id]" class="form-select form-select-sm" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->name }} ({{ strtoupper($acc->account_type) }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="lines[1][party_id]" class="form-select form-select-sm">
                                        <option value="">— None —</option>
                                        @foreach($parties as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="lines[1][description]" class="form-control form-control-sm" placeholder="Line remarks...">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[1][debit]" class="form-control form-control-sm text-end line-dr" value="0.00" min="0">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[1][credit]" class="form-control form-control-sm text-end line-cr" value="0.00" min="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-line p-0"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Totals Footer -->
            <div class="card-footer bg-light p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div id="balance_indicator" class="fw-bold text-success">
                            <i class="fas fa-check-circle me-1"></i> Balanced (Debits = Credits)
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="me-4">Total Debit: <strong class="text-success fs-5" id="total_dr">₹0.00</strong></span>
                        <span>Total Credit: <strong class="text-danger fs-5" id="total_cr">₹0.00</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('admin.accounting.journals.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success" id="submitBtn">
                <i class="fas fa-check-circle me-1"></i> Post Journal Voucher
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lineIdx = 2;

    function calculateJournalTotals() {
        let totalDr = 0;
        let totalCr = 0;

        document.querySelectorAll('#linesBody .line-row').forEach(function(row) {
            const dr = parseFloat(row.querySelector('.line-dr').value) || 0;
            const cr = parseFloat(row.querySelector('.line-cr').value) || 0;
            totalDr += dr;
            totalCr += cr;
        });

        document.getElementById('total_dr').textContent = '₹' + totalDr.toFixed(2);
        document.getElementById('total_cr').textContent = '₹' + totalCr.toFixed(2);

        const diff = Math.abs(totalDr - totalCr);
        const indicator = document.getElementById('balance_indicator');
        const submitBtn = document.getElementById('submitBtn');

        if (totalDr > 0 && diff < 0.01) {
            indicator.innerHTML = '<i class="fas fa-check-circle me-1 text-success"></i> Balanced (Debits = Credits)';
            indicator.className = 'fw-bold text-success';
            submitBtn.disabled = false;
        } else {
            indicator.innerHTML = `<i class="fas fa-exclamation-triangle me-1 text-danger"></i> Unbalanced Difference: ₹${diff.toFixed(2)}`;
            indicator.className = 'fw-bold text-danger';
            submitBtn.disabled = (totalDr === 0);
        }
    }

    document.getElementById('addLineBtn').addEventListener('click', function() {
        const template = `
            <tr class="line-row">
                <td>
                    <select name="lines[${lineIdx}][account_id]" class="form-select form-select-sm" required>
                        <option value="">Select Account</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->name }} ({{ strtoupper($acc->account_type) }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="lines[${lineIdx}][party_id]" class="form-select form-select-sm">
                        <option value="">— None —</option>
                        @foreach($parties as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="lines[${lineIdx}][description]" class="form-control form-control-sm" placeholder="Line remarks...">
                </td>
                <td>
                    <input type="number" step="0.01" name="lines[${lineIdx}][debit]" class="form-control form-control-sm text-end line-dr" value="0.00" min="0">
                </td>
                <td>
                    <input type="number" step="0.01" name="lines[${lineIdx}][credit]" class="form-control form-control-sm text-end line-cr" value="0.00" min="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger remove-line p-0"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        document.getElementById('linesBody').insertAdjacentHTML('beforeend', template);
        lineIdx++;
        calculateJournalTotals();
    });

    document.getElementById('linesBody').addEventListener('input', calculateJournalTotals);

    document.getElementById('linesBody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-line')) {
            if (document.querySelectorAll('#linesBody .line-row').length > 2) {
                e.target.closest('.line-row').remove();
                calculateJournalTotals();
            } else {
                alert('A double-entry voucher requires at least two lines.');
            }
        }
    });

    calculateJournalTotals();
});
</script>
@endpush
@endsection
