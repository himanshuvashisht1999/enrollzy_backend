@extends('admin.layouts.master')

@section('title', 'Create New Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New Invoice</h4>
    <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.billing.invoices.store') }}" method="POST">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Organisation <span class="text-danger">*</span></label>
                    <select name="organisation_id" id="organisation_id" class="form-select @error('organisation_id') is-invalid @enderror" required>
                        <option value="">Select Organisation...</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organisation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Campus <span class="text-danger">*</span></label>
                    <select name="campus_id" id="campus_id" class="form-select @error('campus_id') is-invalid @enderror" required disabled>
                        <option value="">Select Campus...</option>
                    </select>
                    @error('campus_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                    @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Campus Details Preview -->
            <div class="row mb-4 d-none" id="campusDetailsPreview">
                <div class="col-12">
                    <div class="p-3 bg-light border rounded">
                        <h6 class="mb-1 text-primary fw-bold" id="previewCampusName"></h6>
                        <div class="text-muted small" id="previewCampusAddress"></div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Invoice Items -->
            <div class="card mb-4 border shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
                    <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                        <i class="fas fa-plus"></i> Add Services
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="invoiceItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Service</th>
                                    <th width="30%">Description</th>
                                    <th width="10%">Qty</th>
                                    <th width="15%">Unit Price</th>
                                    <th width="20%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemsBody">
                                <!-- Dynamic rows will be inserted here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="subtotal" id="calcSubtotal" class="form-control text-end bg-light" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Discount:</td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="discount_amount" id="calcDiscount" class="form-control text-end" value="0.00"></td>
                                </tr>
                                {{-- GST fields hidden as requested --}}
                                <tr class="d-none">
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="me-2">CGST (%):</span>
                                            <input type="number" step="0.01" id="cgstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="0">
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="cgst_amount" id="calcCgst" class="form-control text-end bg-light" readonly></td>
                                </tr>
                                <tr class="d-none">
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="me-2">SGST (%):</span>
                                            <input type="number" step="0.01" id="sgstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="0">
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="sgst_amount" id="calcSgst" class="form-control text-end bg-light" readonly></td>
                                </tr>
                                <tr class="d-none">
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="checkbox" id="useIgst" aria-label="Use IGST instead of CGST/SGST">
                                            <span class="me-2">IGST (%):</span>
                                            <input type="number" step="0.01" id="igstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="0">
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end">
                                        <input type="number" step="0.01" name="igst_amount" id="calcIgst" class="form-control text-end bg-light" value="0.00" readonly>
                                    </td>
                                </tr>
                                <input type="hidden" name="total_tax" id="calcTotalTax">
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold fs-5">Grand Total:</td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="total_amount" id="calcTotal" class="form-control text-end fw-bold fs-5 bg-light" readonly></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Notes / Terms & Conditions</label>
                <textarea name="notes" rows="3" class="form-control">Payment is due within 7 days. Late payments may incur additional charges.</textarea>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary" id="saveInvoiceBtn"><i class="fas fa-save"></i> Generate Invoice</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const services = @json($services);
    
    document.addEventListener('DOMContentLoaded', function() {
        let rowIndex = 0;
        
        const tbody = document.getElementById('invoiceItemsBody');
        const addItemBtn = document.getElementById('addItemBtn');
        const discountInput = document.getElementById('calcDiscount');
        const useIgstCheck = document.getElementById('useIgst');
        const form = document.querySelector('form');
        
        const orgSelect = document.getElementById('organisation_id');
        const campusSelect = document.getElementById('campus_id');
        const campusPreview = document.getElementById('campusDetailsPreview');
        const previewName = document.getElementById('previewCampusName');
        const previewAddress = document.getElementById('previewCampusAddress');
        let currentCampuses = [];

        $('#organisation_id').on('change', function() {
            const orgId = $(this).val();
            const $campusSelect = $('#campus_id');
            const $campusPreview = $('#campusDetailsPreview');
            
            $campusSelect.empty().append('<option value="">Select Campus...</option>');
            $campusSelect.prop('disabled', true);
            $campusPreview.addClass('d-none');
            $campusSelect.trigger('change.select2');
            
            if(orgId) {
                fetch(`/admin/organisations/${orgId}/campuses-json`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        currentCampuses = data;
                        if(data.length > 0) {
                            data.forEach(campus => {
                                $campusSelect.append(new Option(campus.campus_name, campus.id));
                            });
                            $campusSelect.prop('disabled', false);
                        }
                        $campusSelect.trigger('change.select2');
                    });
            }
        });

        $('#campus_id').on('change', function() {
            const campusId = $(this).val();
            const $campusPreview = $('#campusDetailsPreview');
            
            if(campusId) {
                const campus = currentCampuses.find(c => c.id == campusId);
                if(campus) {
                    $('#previewCampusName').text(campus.campus_name);
                    let address = [];
                    if(campus.full_address) address.push(campus.full_address);
                    if(campus.city) address.push(campus.city);
                    if(campus.state) address.push(campus.state);
                    if(campus.pincode) address.push(campus.pincode);
                    $('#previewCampusAddress').text(address.join(', '));
                    $campusPreview.removeClass('d-none');
                } else {
                    $campusPreview.addClass('d-none');
                }
            } else {
                $campusPreview.addClass('d-none');
            }
        });
        
        function addNewRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="items[${rowIndex}][service_id]" class="form-select service-select" required>
                        <option value="">Select Service...</option>
                        ${services.map(s => `<option value="${s.id}" data-price="${s.sale_price || s.price}">${s.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="text" name="items[${rowIndex}][description]" class="form-control item-desc" required></td>
                <td><input type="number" min="1" name="items[${rowIndex}][quantity]" class="form-control item-qty text-center" value="1" required></td>
                <td><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control item-price text-end" required></td>
                <td><input type="number" step="0.01" name="items[${rowIndex}][total]" class="form-control item-total text-end bg-light" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
            `;
            tbody.appendChild(tr);
            rowIndex++;
            
            // Initialize Select2 on the new row
            $(tr).find('.service-select').select2({ width: '100%' });
            
            attachRowListeners(tr);
            calculateTotals();
        }
        
        function attachRowListeners(row) {
            const serviceSelect = row.querySelector('.service-select');
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const removeBtn = row.querySelector('.remove-row');
            const descInput = row.querySelector('.item-desc');
            
            // Bind using jQuery to catch Select2 events
            $(serviceSelect).on('change', function() {
                const option = this.options[this.selectedIndex];
                if(option && option.value) {
                    descInput.value = option.text;
                    priceInput.value = parseFloat(option.dataset.price).toFixed(2);
                } else {
                    descInput.value = '';
                    priceInput.value = '';
                }
                calculateRowTotal(row);
            });
            
            qtyInput.addEventListener('input', () => calculateRowTotal(row));
            priceInput.addEventListener('input', () => calculateRowTotal(row));
            
            removeBtn.addEventListener('click', function() {
                row.remove();
                calculateTotals();
            });
        }
        
        function calculateRowTotal(row) {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = qty * price;
            row.querySelector('.item-total').value = total.toFixed(2);
            calculateTotals();
        }
        
        function calculateTotals() {
            let subtotal = 0;
            
            document.querySelectorAll('#invoiceItemsBody tr').forEach(row => {
                const total = parseFloat(row.querySelector('.item-total').value) || 0;
                subtotal += total;
            });
            
            const discount = parseFloat(discountInput.value) || 0;
            const taxableAmount = subtotal > discount ? subtotal - discount : 0;
            
            const cgstRate = parseFloat(document.getElementById('cgstRate').value) || 0;
            const sgstRate = parseFloat(document.getElementById('sgstRate').value) || 0;
            const igstRate = parseFloat(document.getElementById('igstRate').value) || 0;
            
            let cgst = 0, sgst = 0, igst = 0;
            if(useIgstCheck.checked) {
                igst = taxableAmount * (igstRate / 100);
            } else {
                cgst = taxableAmount * (cgstRate / 100);
                sgst = taxableAmount * (sgstRate / 100);
            }
            
            const adjustedTaxAmount = cgst + sgst + igst;
            
            document.getElementById('calcSubtotal').value = subtotal.toFixed(2);
            document.getElementById('calcCgst').value = cgst.toFixed(2);
            document.getElementById('calcSgst').value = sgst.toFixed(2);
            document.getElementById('calcIgst').value = igst.toFixed(2);
            document.getElementById('calcTotalTax').value = adjustedTaxAmount.toFixed(2);
            
            const grandTotal = taxableAmount + adjustedTaxAmount;
            document.getElementById('calcTotal').value = grandTotal.toFixed(2);
            
            document.getElementById('saveInvoiceBtn').disabled = subtotal <= 0;
        }
        
        addItemBtn.addEventListener('click', addNewRow);
        discountInput.addEventListener('input', calculateTotals);
        useIgstCheck.addEventListener('change', calculateTotals);

        ['input', 'change', 'keyup'].forEach(evt => {
            document.getElementById('cgstRate').addEventListener(evt, calculateTotals);
            document.getElementById('sgstRate').addEventListener(evt, calculateTotals);
            document.getElementById('igstRate').addEventListener(evt, calculateTotals);
        });
        
        form.addEventListener('submit', function(e) {
            if(document.querySelectorAll('#invoiceItemsBody tr').length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the invoice.');
            }
        });
        
        // Add one empty row on start
        addNewRow();
    });
</script>
@endsection
