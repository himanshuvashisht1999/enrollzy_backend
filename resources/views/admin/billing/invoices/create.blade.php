@extends('admin.layouts.master')

@section('title', 'Create New Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-dark fw-bold">Create New Invoice</h4>
        <p class="text-muted small mb-0">Generate a billing invoice for an Organisation or Client.</p>
    </div>
    <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Invoices
    </a>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</div>
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.billing.invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            
            <!-- Recipient Type Switcher -->
            <div class="card bg-light border-0 mb-4">
                <div class="card-body py-3">
                    <label class="form-label fw-bold text-dark mb-2">Invoice To (Recipient Type) <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="client_type" id="typeOrganisation" value="organisation" {{ old('client_type', 'organisation') === 'organisation' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="typeOrganisation">
                                <i class="fas fa-university text-primary me-1"></i> Organisation
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="client_type" id="typeClient" value="client" {{ old('client_type') === 'client' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="typeClient">
                                <i class="fas fa-user-tie text-success me-1"></i> Client
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Organisation Section -->
                <div class="col-md-3 mb-3 organisation-field">
                    <label class="form-label fw-semibold">Organisation <span class="text-danger">*</span></label>
                    <select name="organisation_id" id="organisation_id" class="form-select @error('organisation_id') is-invalid @enderror">
                        <option value="">Select Organisation...</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}" {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organisation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3 organisation-field">
                    <label class="form-label fw-semibold">Campus <span class="text-danger" id="campusRequiredMarker" style="display: none;">*</span></label>
                    <select name="campus_id" id="campus_id" class="form-select @error('campus_id') is-invalid @enderror" disabled>
                        <option value="">Select Campus...</option>
                    </select>
                    @error('campus_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Client Section -->
                <div class="col-md-6 mb-3 client-field d-none">
                    <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                    <select name="billing_client_id" id="billing_client_id" class="form-select @error('billing_client_id') is-invalid @enderror">
                        <option value="">Select Client...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" 
                                    data-company-type="{{ $c->company_type }}"
                                    data-contact="{{ $c->contact_person }}"
                                    data-email="{{ $c->email }}"
                                    data-phone="{{ $c->phone }}"
                                    data-gstin="{{ $c->gstin }}"
                                    data-tan="{{ $c->tan_number }}"
                                    data-pan="{{ $c->pan_number }}"
                                    data-cin="{{ $c->cin_number }}"
                                    data-address="{{ $c->address }}"
                                    data-city="{{ $c->city }}"
                                    data-state="{{ $c->state }}"
                                    data-pincode="{{ $c->pincode }}"
                                    data-country="{{ $c->country }}"
                                    {{ old('billing_client_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->company_type ? '(' . $c->company_type . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('billing_client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text small"><a href="{{ route('admin.billing.clients.create') }}" target="_blank" class="text-decoration-none"><i class="fas fa-plus-circle me-1"></i>Add New Client</a> (opens in new tab)</div>
                </div>

                <!-- Dates -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                    @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Campus Details Preview -->
            <div class="row mb-4 d-none" id="campusDetailsPreview">
                <div class="col-12">
                    <div class="p-3 bg-light border rounded">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary">Campus Info</span>
                            <h6 class="mb-0 text-primary fw-bold" id="previewCampusName"></h6>
                        </div>
                        <div class="text-muted small" id="previewCampusAddress"></div>
                    </div>
                </div>
            </div>

            <!-- Client Details Preview -->
            <div class="row mb-4 d-none" id="clientDetailsPreview">
                <div class="col-12">
                    <div class="p-3 bg-light border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-success">Client Info</span>
                                    <h6 class="mb-0 text-dark fw-bold" id="previewClientName"></h6>
                                    <span class="badge bg-secondary-subtle text-secondary border" id="previewClientType"></span>
                                </div>
                                <div class="text-muted small" id="previewClientContact"></div>
                                <div class="text-muted small" id="previewClientAddress"></div>
                            </div>
                            <div class="text-end small" id="previewClientTax">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Invoice Items -->
            <div class="card mb-4 border shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Invoice Items</h5>
                    <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                        <i class="fas fa-plus me-1"></i> Add Services
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="invoiceItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="22%">Service</th>
                                    <th width="28%">Description</th>
                                    <th width="10%">Qty</th>
                                    <th width="15%">Unit Price</th>
                                    <th width="20%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemsBody">
                                <!-- Dynamic rows inserted here -->
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
                                {{-- GST fields --}}
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="me-2">CGST (%):</span>
                                            <input type="number" step="0.01" id="cgstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="9">
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="cgst_amount" id="calcCgst" class="form-control text-end bg-light" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <span class="me-2">SGST (%):</span>
                                            <input type="number" step="0.01" id="sgstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="9">
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-end"><input type="number" step="0.01" name="sgst_amount" id="calcSgst" class="form-control text-end bg-light" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="checkbox" id="useIgst" aria-label="Use IGST instead of CGST/SGST">
                                            <span class="me-2">IGST (%):</span>
                                            <input type="number" step="0.01" id="igstRate" class="form-control form-control-sm text-end" style="width: 80px;" value="18">
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
                <label class="form-label fw-semibold">Notes / Terms & Conditions</label>
                <textarea name="notes" rows="3" class="form-control">Payment is due within 7 days. Late payments may incur additional charges.</textarea>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4 py-2" id="saveInvoiceBtn"><i class="fas fa-save me-1"></i> Generate Invoice</button>
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
        const form = document.getElementById('invoiceForm');
        let currentCampuses = [];

        // Type switcher (Organisation vs Client)
        function toggleRecipientType(type) {
            if (type === 'client') {
                $('.organisation-field').addClass('d-none');
                $('#organisation_id, #campus_id').prop('required', false);
                $('#campusDetailsPreview').addClass('d-none');

                $('.client-field').removeClass('d-none');
                $('#billing_client_id').prop('required', true);
                updateClientPreview();
            } else {
                $('.client-field').addClass('d-none');
                $('#billing_client_id').prop('required', false);
                $('#clientDetailsPreview').addClass('d-none');

                $('.organisation-field').removeClass('d-none');
                $('#organisation_id').prop('required', true);
                if (currentCampuses.length > 0) {
                    $('#campus_id').prop('required', true);
                    $('#campusRequiredMarker').show();
                } else {
                    $('#campus_id').prop('required', false);
                    $('#campusRequiredMarker').hide();
                }
            }
        }

        $('input[name="client_type"]').on('change', function() {
            toggleRecipientType($(this).val());
        });

        // Client preview handler
        function updateClientPreview() {
            const $select = $('#billing_client_id');
            const $selected = $select.find(':selected');
            const clientId = $select.val();
            const $preview = $('#clientDetailsPreview');

            if (clientId && $selected.length) {
                $('#previewClientName').text($selected.text().split('(')[0].trim());
                const companyType = $selected.data('company-type');
                if (companyType) {
                    $('#previewClientType').text(companyType).removeClass('d-none');
                } else {
                    $('#previewClientType').addClass('d-none');
                }

                let contactParts = [];
                const contact = $selected.data('contact');
                const email = $selected.data('email');
                const phone = $selected.data('phone');
                if (contact) contactParts.push(`<strong>Contact:</strong> ${contact}`);
                if (email) contactParts.push(`<strong>Email:</strong> ${email}`);
                if (phone) contactParts.push(`<strong>Phone:</strong> ${phone}`);
                $('#previewClientContact').html(contactParts.join(' | ') || 'No contact details available');

                let addressParts = [];
                const addr = $selected.data('address');
                const city = $selected.data('city');
                const state = $selected.data('state');
                const pincode = $selected.data('pincode');
                const country = $selected.data('country');
                if (addr) addressParts.push(addr);
                if (city) addressParts.push(city);
                if (state) addressParts.push(state);
                if (pincode) addressParts.push(pincode);
                if (country && country !== 'India') addressParts.push(country);
                $('#previewClientAddress').html(addressParts.length ? `<strong>Address:</strong> ${addressParts.join(', ')}` : '');

                let taxParts = [];
                const gstin = $selected.data('gstin');
                const tan = $selected.data('tan');
                const pan = $selected.data('pan');
                const cin = $selected.data('cin');
                if (gstin) taxParts.push(`<div><strong>GSTIN:</strong> <span class="font-monospace">${gstin}</span></div>`);
                if (tan) taxParts.push(`<div><strong>TAN:</strong> <span class="font-monospace">${tan}</span></div>`);
                if (pan) taxParts.push(`<div><strong>PAN:</strong> <span class="font-monospace">${pan}</span></div>`);
                if (cin) taxParts.push(`<div><strong>CIN:</strong> <span class="font-monospace">${cin}</span></div>`);
                $('#previewClientTax').html(taxParts.join(''));

                $preview.removeClass('d-none');
            } else {
                $preview.addClass('d-none');
            }
        }

        $('#billing_client_id').on('change', updateClientPreview);

        // Organisation & Campus Ajax
        function loadCampuses(orgId, selectedCampusId) {
            const $campusSelect = $('#campus_id');
            const $campusPreview = $('#campusDetailsPreview');
            const $marker = $('#campusRequiredMarker');
            
            $campusSelect.empty().append('<option value="">Select Campus...</option>');
            $campusSelect.prop('disabled', true);
            $campusPreview.addClass('d-none');
            
            if(!orgId) {
                currentCampuses = [];
                $campusSelect.prop('required', false);
                $marker.hide();
                return;
            }

            fetch(`/admin/organisations/${orgId}/campuses-json`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    currentCampuses = data || [];
                    if(currentCampuses.length > 0) {
                        $campusSelect.empty().append('<option value="">Select Campus...</option>');
                        currentCampuses.forEach(campus => {
                            const isSelected = selectedCampusId && (campus.id == selectedCampusId);
                            $campusSelect.append(new Option(campus.campus_name, campus.id, isSelected, isSelected));
                        });
                        $campusSelect.prop('disabled', false);
                        if ($('input[name="client_type"]:checked').val() === 'organisation') {
                            $campusSelect.prop('required', true);
                            $marker.show();
                        }
                        if (selectedCampusId) {
                            $campusSelect.trigger('change');
                        }
                    } else {
                        // Organisation has no campus
                        $campusSelect.empty().append('<option value="">No Campus Available</option>');
                        $campusSelect.prop('disabled', false);
                        $campusSelect.prop('required', false);
                        $campusSelect.removeClass('is-invalid');
                        $campusSelect.siblings('.invalid-feedback').hide();
                        $marker.hide();
                        $campusPreview.addClass('d-none');
                    }
                })
                .catch(() => {
                    currentCampuses = [];
                    $campusSelect.prop('required', false);
                    $marker.hide();
                });
        }

        $('#organisation_id').on('change', function() {
            loadCampuses($(this).val(), null);
        });

        // Initialize state based on checked radio
        toggleRecipientType($('input[name="client_type"]:checked').val());

        // Initial campus load if organisation is pre-selected (e.g. from validation errors)
        const initialOrgId = $('#organisation_id').val();
        const initialCampusId = "{{ old('campus_id') }}";
        if (initialOrgId) {
            loadCampuses(initialOrgId, initialCampusId);
        } else {
            $('#campusRequiredMarker').hide();
        }

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
            
            attachRowListeners(tr);
            calculateTotals();
        }
        
        function attachRowListeners(row) {
            const serviceSelect = row.querySelector('.service-select');
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const removeBtn = row.querySelector('.remove-row');
            const descInput = row.querySelector('.item-desc');
            
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
        
        // Add first row on start
        addNewRow();
    });
</script>
@endsection
