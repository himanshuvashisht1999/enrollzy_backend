@extends('admin.layouts.master')

@section('title', 'Record Vendor Bill')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Bills
        </a>
        <h3 class="fw-bold mb-1">Record Vendor Bill</h3>
        <p class="text-muted small mb-0">Record accounts payable bill with expense account allocation, input GST credit, and TDS withholding.</p>
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

    <form action="{{ route('admin.accounting.bills.store') }}" method="POST" id="billForm">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Vendor Bill Header</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Vendor (Supplier / Contractor) <span class="text-danger">*</span></label>
                        <select name="party_id" id="party_select" class="form-select" required>
                            <option value="">Select Vendor</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" data-state="{{ $p->state }}" data-statecode="{{ $p->state_code }}" data-gstin="{{ $p->gstin }}" {{ old('party_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->state ? "({$p->state})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="party_details" class="small text-muted mt-1"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Vendor Invoice / Bill Ref #</label>
                        <input type="text" name="vendor_bill_ref" class="form-control" placeholder="e.g. AWS-INV-89342" value="{{ old('vendor_bill_ref') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Bill Date <span class="text-danger">*</span></label>
                        <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">TDS Withholding Section (India Income Tax)</label>
                        <select name="tds_rate_id" id="tds_select" class="form-select">
                            <option value="" data-rate="0">No TDS Applicable</option>
                            @foreach($tdsRates as $tds)
                                <option value="{{ $tds->id }}" data-rate="{{ $tds->rate }}" {{ old('tds_rate_id') == $tds->id ? 'selected' : '' }}>
                                    {{ $tds->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Notes / Description</label>
                        <input type="text" name="notes" class="form-control" placeholder="Remarks or internal PO reference" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Bill Line Items & Expense Distribution</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                    <i class="fas fa-plus me-1"></i> Add Line Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th style="width: 25%;">Item & Description</th>
                                <th style="width: 10%;">HSN/SAC</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 12%;">Unit Price (₹)</th>
                                <th style="width: 13%;">Input GST Rate</th>
                                <th style="width: 18%;">Expense Category Account</th>
                                <th class="text-end" style="width: 12%;">Taxable Amt (₹)</th>
                                <th style="width: 3%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td>
                                    <input type="text" name="items[0][item_name]" class="form-control form-control-sm mb-1" placeholder="Product / Service Name" required>
                                    <input type="text" name="items[0][description]" class="form-control form-control-sm text-muted" placeholder="Optional details...">
                                </td>
                                <td>
                                    <input type="text" name="items[0][hsn_sac]" class="form-control form-control-sm" placeholder="e.g. 998315">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty" value="1.00" min="0.01" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm item-price" value="0.00" min="0" required>
                                </td>
                                <td>
                                    <select name="items[0][tax_rate_id]" class="form-select form-select-sm item-tax">
                                        <option value="" data-rate="0">GST 0% / None</option>
                                        @foreach($taxRates as $tax)
                                            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" {{ $tax->rate == 18 ? 'selected' : '' }}>
                                                {{ $tax->name }} ({{ $tax->rate }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][expense_account_id]" class="form-select form-select-sm">
                                        @foreach($expenseAccounts as $exp)
                                            <option value="{{ $exp->id }}">{{ $exp->account_code }} - {{ $exp->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end fw-bold item-taxable">
                                    ₹0.00
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-item p-0"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Totals Footer -->
            <div class="card-footer bg-light p-4">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless text-end">
                            <tr>
                                <td class="fw-bold">Subtotal (Expense Value):</td>
                                <td class="fw-bold" id="summary_subtotal">₹0.00</td>
                            </tr>
                            <tr id="cgst_row">
                                <td class="text-muted">Input CGST Credit:</td>
                                <td id="summary_cgst">₹0.00</td>
                            </tr>
                            <tr id="sgst_row">
                                <td class="text-muted">Input SGST Credit:</td>
                                <td id="summary_sgst">₹0.00</td>
                            </tr>
                            <tr id="igst_row" style="display: none;">
                                <td class="text-muted">Input IGST Credit:</td>
                                <td id="summary_igst">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-dark">Total Bill Amount:</td>
                                <td class="fw-bold text-dark" id="summary_total">₹0.00</td>
                            </tr>
                            <tr class="text-warning">
                                <td class="fw-bold">Less: TDS Withheld:</td>
                                <td class="fw-bold" id="summary_tds">- ₹0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold fs-5 text-danger">Net Payable to Vendor:</td>
                                <td class="fw-bold fs-5 text-danger" id="summary_net_payable">₹0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                <i class="fas fa-save me-1"></i> Save as Draft
            </button>
            <button type="submit" name="action" value="save_and_post" class="btn btn-success">
                <i class="fas fa-paper-plane me-1"></i> Save & Post to Ledger
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const companyStateCode = '07';

    function isInterState() {
        const select = document.getElementById('party_select');
        const opt = select.options[select.selectedIndex];
        if (!opt) return false;
        const code = opt.getAttribute('data-statecode') || '';
        return code && code !== companyStateCode;
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalCgst = 0;
        let totalSgst = 0;
        let totalIgst = 0;
        const interState = isInterState();

        document.querySelectorAll('#itemsBody .item-row').forEach(function(row) {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const taxable = qty * price;

            const taxSelect = row.querySelector('.item-tax');
            const taxOpt = taxSelect.options[taxSelect.selectedIndex];
            const rate = taxOpt ? (parseFloat(taxOpt.getAttribute('data-rate')) || 0) : 0;

            row.querySelector('.item-taxable').textContent = '₹' + taxable.toFixed(2);
            subtotal += taxable;

            if (rate > 0) {
                if (interState) {
                    totalIgst += (taxable * rate) / 100;
                } else {
                    totalCgst += (taxable * (rate / 2)) / 100;
                    totalSgst += (taxable * (rate / 2)) / 100;
                }
            }
        });

        // TDS
        const tdsSelect = document.getElementById('tds_select');
        const tdsOpt = tdsSelect.options[tdsSelect.selectedIndex];
        const tdsRate = tdsOpt ? (parseFloat(tdsOpt.getAttribute('data-rate')) || 0) : 0;
        const tdsAmount = (subtotal * tdsRate) / 100;

        const grandTotal = subtotal + totalCgst + totalSgst + totalIgst;
        const netPayable = Math.max(0, grandTotal - tdsAmount);

        document.getElementById('summary_subtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('summary_cgst').textContent = '₹' + totalCgst.toFixed(2);
        document.getElementById('summary_sgst').textContent = '₹' + totalSgst.toFixed(2);
        document.getElementById('summary_igst').textContent = '₹' + totalIgst.toFixed(2);
        document.getElementById('summary_total').textContent = '₹' + grandTotal.toFixed(2);
        document.getElementById('summary_tds').textContent = '- ₹' + tdsAmount.toFixed(2);
        document.getElementById('summary_net_payable').textContent = '₹' + netPayable.toFixed(2);

        if (interState) {
            document.getElementById('cgst_row').style.display = 'none';
            document.getElementById('sgst_row').style.display = 'none';
            document.getElementById('igst_row').style.display = 'table-row';
        } else {
            document.getElementById('cgst_row').style.display = 'table-row';
            document.getElementById('sgst_row').style.display = 'table-row';
            document.getElementById('igst_row').style.display = 'none';
        }
    }

    document.getElementById('party_select').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            const state = opt.getAttribute('data-state') || '';
            const gstin = opt.getAttribute('data-gstin') || 'Unregistered';
            document.getElementById('party_details').textContent = 'GSTIN: ' + gstin + ' | State: ' + state;
        } else {
            document.getElementById('party_details').textContent = '';
        }
        calculateTotals();
    });

    document.getElementById('tds_select').addEventListener('change', calculateTotals);

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const template = `
            <tr class="item-row">
                <td>
                    <input type="text" name="items[${rowIndex}][item_name]" class="form-control form-control-sm mb-1" placeholder="Product / Service Name" required>
                    <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm text-muted" placeholder="Optional details...">
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][hsn_sac]" class="form-control form-control-sm" placeholder="e.g. 998315">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control form-control-sm item-qty" value="1.00" min="0.01" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm item-price" value="0.00" min="0" required>
                </td>
                <td>
                    <select name="items[${rowIndex}][tax_rate_id]" class="form-select form-select-sm item-tax">
                        <option value="" data-rate="0">GST 0% / None</option>
                        @foreach($taxRates as $tax)
                            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" {{ $tax->rate == 18 ? 'selected' : '' }}>
                                {{ $tax->name }} ({{ $tax->rate }}%)
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[${rowIndex}][expense_account_id]" class="form-select form-select-sm">
                        @foreach($expenseAccounts as $exp)
                            <option value="{{ $exp->id }}">{{ $exp->account_code }} - {{ $exp->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-end fw-bold item-taxable">
                    ₹0.00
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger remove-item p-0"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', template);
        rowIndex++;
        calculateTotals();
    });

    document.getElementById('itemsBody').addEventListener('input', calculateTotals);
    document.getElementById('itemsBody').addEventListener('change', calculateTotals);

    document.getElementById('itemsBody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            if (document.querySelectorAll('#itemsBody .item-row').length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('At least one line item is required.');
            }
        }
    });

    calculateTotals();
});
</script>
@endpush
@endsection
