@extends('admin.layouts.master')

@section('title', 'Calling Module')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .radio-group { display: flex; align-items: center; gap: 20px; }
    input[type="radio"] { transform: scale(1.3); margin-right: 5px; }
    .radio-group label { font-size: 1rem; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="form-group col-lg-3 mb-3">
        <div class="radio-group">
            <div>
                <input type="radio" id="option1" name="group" value="1" {{ request('group') != 2 ? 'checked' : '' }}>
                <label for="option1">Admin Data</label>
            </div>
            <div>
                <input type="radio" id="option2" name="group" value="2" {{ request('group') == 2 ? 'checked' : '' }}>
                <label for="option2">Private Data</label>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Find Calling User</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row" action="{{ route('admin.students-crm.calling-module.index') }}" method="GET">
                <input type="hidden" name="group" value="{{ request('group', 1) }}">
                
                <div class="form-group col-lg-3 mb-3">
                    <select name="category" class="form-select rounded-3" id="categoryFilter">
                        <option value="">Select Categories</option>
                        @php
                            function renderCategoryOptions($categories, $level = 0) {
                                foreach ($categories as $cat) {
                                    echo '<option value="'.$cat->id.'"'.
                                        (request('category') == $cat->id ? ' selected' : '').
                                        '>';
                                    echo str_repeat("— ", $level).$cat->name;
                                    echo '</option>';
                                    if ($cat->childrenRecursive && $cat->childrenRecursive->count()) {
                                        renderCategoryOptions($cat->childrenRecursive, $level + 1);
                                    }
                                }
                            }
                        @endphp
                        @php renderCategoryOptions($categories); @endphp
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <select name="country" class="form-select rounded-3" id="countryFilter">
                        <option value="">Select Country</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <select name="state" class="form-select rounded-3" id="stateFilter">
                        <option value="">Select State</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <select name="city" class="form-select rounded-3" id="cityFilter">
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <input type="text" name="filter_name" id="nameFilter" class="form-control rounded-3" placeholder="Search by Full Name">
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <input type="text" name="filter_phone" id="phoneFilter" class="form-control rounded-3" placeholder="Search by Phone Number">
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <span class="d-block mb-2">User Without Status</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleUserWithoutStatus" name="user_with_out_status" value="1" {{ $user_with_out_status == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="toggleUserWithoutStatus">
                            <span id="toggleLabel">{{ $user_with_out_status == 1 ? 'Yes' : 'No' }}</span>
                        </label>
                    </div>
                </div>

                <div class="form-group col-lg-3 mb-3">
                    <label class="d-block mb-2">Sequence Calling</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="toggleSequence" name="sequence_mode" value="1" {{ request('sequence_mode') == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="toggleSequence">
                            <span id="sequenceLabel">{{ request('sequence_mode') == 1 ? 'ON (Pending only)' : 'OFF (Normal)' }}</span>
                        </label>
                    </div>
                </div>

                <div class="col-lg-12 d-flex gap-2">
                    <button class="btn btn-primary px-4 rounded-pill" type="submit" id="submitSearchButton">Search</button>
                    <a href="{{ route('admin.students-crm.calling-module.index') }}" class="btn btn-info px-4 rounded-pill text-white" id="resetBtn">Reset</a>
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" id="restartBtn">Re-Start</button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($data) && $data->count() > 0)
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Calling User</h6>
            <span class="text-muted small">Total number of contacts are <span id="totalContacts">{{ $count }}</span></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Student / Contact</th>
                            <th>Category</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td><b>{{ $item->name }}</b><br><small class="text-muted">{{ $item->phone }}</small></td>
                            <td>{{ $item->category->name ?? '<span class="text-muted small">No Category</span>' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-soft-primary open-calling-modal" 
                                    data-id="{{ $item->id }}" 
                                    data-name="{{ $item->name }}" 
                                    data-phone="{{ $item->phone }}" 
                                    data-category="{{ $item->category_id ?? '' }}">
                                    <i class="fas fa-phone-alt"></i> Update Status
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @elseif(request()->has('category') || request()->has('country') || request()->has('state') || request()->has('city') || request()->has('filter_name') || request()->has('filter_phone') || request()->has('sequence_mode') || request()->has('user_with_out_status'))
    <div class="alert alert-info mt-3 shadow-sm border-0 rounded-4">No more users found for the selected filters.</div>
    @endif
</div>

<!-- Update Calling Status Modal -->
<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Update Calling Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="callForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="group_id" value="{{ request('group', 1) }}">
                <input type="hidden" id="customer_id" name="customer_id">
                <input type="hidden" id="user_phone" name="user_phone">
                <input type="hidden" id="category_val" name="category">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" class="form-control rounded-3" name="name" id="user_name" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-bold">Call Status <span class="text-danger">*</span></label>
                            <select name="status_id" class="form-select rounded-3" id="status_id" required>
                                <option value="" selected disabled>Select</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" data-action="{{ $status->calling_action_id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3" id="date-field">
                            <label class="form-label small fw-bold">Reminder Date</label>
                            <input type="date" name="next_call_date" class="form-control rounded-3" id="call_date">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-bold">Action Taken <span class="text-danger">*</span></label>
                            <select name="action_id" id="action_id" class="form-select rounded-3" required>
                                <option value="">Select Action</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action->id }}">{{ $action->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold">Comments</label>
                            <textarea id="message" name="remark" class="form-control rounded-3" rows="3" placeholder="Add Comments Here..."></textarea>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_whatsapp_message" name="is_whatsapp_message" value="1">
                                <label class="form-check-label fw-bold" for="is_whatsapp_message">
                                    Want to send whatsapp message?
                                </label>
                            </div>
                        </div>

                        <div id="whatsapp_fields" class="col-12" style="display:none;">
                            <div class="row g-3 mt-1">
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Template</label>
                                    <select name="whatsapp_template_id" class="form-select rounded-3" id="whatsapp_template_id">
                                        <option value="">Select</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" data-caption="{{ $template->caption }}" data-message="{{ $template->message }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Caption</label>
                                    <input type="text" class="form-control rounded-3" name="caption" id="caption">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Image</label>
                                    <input type="file" class="form-control rounded-3" name="image_whatsapp" accept=".jpg, .jpeg, .png">
                                    <small class="text-muted">Upload an image (jpg, jpeg, png) max 2MB.</small>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Start Time</label>
                                    <input type="datetime-local" class="form-control rounded-3" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>     
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Message</label>
                                    <textarea name="whatsapp_message" class="form-control rounded-3" id="message-editor" placeholder="Enter message"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        $('#restartBtn').on('click', function() {
            let cat = $('#categoryFilter').val();
            if(!cat) {
                Swal.fire('Warning', 'Please select a category first before clicking Re-Start.', 'warning');
                return;
            }
            
            // For now just submit the form with sequence_mode or re-start flag if implemented
            $('#filterForm').submit();
        });

        $(document).on('click', '.open-calling-modal', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let phone = $(this).data('phone');
            let cat = $(this).data('category');

            $('#customer_id').val(id);
            $('#user_name').val(name);
            $('#user_phone').val(phone);
            $('#category_val').val(cat);
            $('#callModal').modal('show');
        });

        $('#callForm').on('submit', function(e) {
            e.preventDefault();
            
            // Sync CKEditor
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].updateElement();
            }

            $.ajax({
                url: "{{ route('admin.students-crm.calling-module.store') }}",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.status == 1) {
                        $('#callModal').modal('hide');
                        $('#callForm')[0].reset();
                        Swal.fire('Interactions Logged', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        // Group Radio
        const radios = document.querySelectorAll('input[type="radio"][name="group"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                const selectedValue = this.value;
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?group=" + selectedValue;
                window.location.href = newUrl;
            });
        });

        // Switches Labels
        $('#toggleUserWithoutStatus').on('change', function() {
            $('#toggleLabel').text(this.checked ? 'Yes' : 'No');
        });
        $('#toggleSequence').on('change', function() {
            $('#sequenceLabel').text(this.checked ? 'ON (Pending only)' : 'OFF (Normal)');
        });

        // Whatsapp Toggle
        $('#is_whatsapp_message').on('change', function() {
            if(this.checked) {
                $('#whatsapp_fields').show();
            } else {
                $('#whatsapp_fields').hide();
                $('#caption').val('');
                if (CKEDITOR.instances['message-editor']) {
                    CKEDITOR.instances['message-editor'].setData('');
                }
            }
        });

        // CKEditor Init
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('message-editor', {});
        }

        $('#whatsapp_template_id').on('change', function() {
            let selected = $(this).find('option:selected');
            let caption = selected.data('caption') || '';
            let msg = selected.data('message') || '';
            
            $('#caption').val(caption);
            if (CKEDITOR.instances['message-editor']) {
                CKEDITOR.instances['message-editor'].setData(msg);
            }
        });

        $('#status_id').on('change', function() {
            let actionId = $(this).find('option:selected').data('action');
            if(actionId) {
                $('#action_id').val(actionId).trigger('change');
            } else {
                $('#action_id').val('').trigger('change');
            }
        });

        // Countries API
        const API_BASE = 'https://countriesnow.space/api/v0.1';
        loadCountries();

        $('#countryFilter').on('change', function () {
            const country = $(this).val();
            $('#stateFilter').html('<option value="">Select State</option>');
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country) loadStates(country);
        });

        $('#stateFilter').on('change', function () {
            const country = $('#countryFilter').val();
            const state   = $(this).val();
            $('#cityFilter').html('<option value="">Select City</option>');
            if (country && state) loadCities(country, state);
        });

        function loadCountries() {
            $.get(API_BASE + '/countries', function(res){
                let html = '<option value="">Select Country</option>';
                res.data.forEach(c => {
                    html += `<option value="${c.country}">${c.country}</option>`;
                });
                $('#countryFilter').html(html);
            });
        }

        function loadStates(country) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/states',
                contentType: 'application/json',
                data: JSON.stringify({ country }),
                success: function(res){
                    let html = '<option value="">Select State</option>';
                    res.data.states.forEach(s => {
                        html += `<option value="${s.name}">${s.name}</option>`;
                    });
                    $('#stateFilter').html(html);
                }
            });
        }

        function loadCities(country, state) {
            $.ajax({
                type: 'POST',
                url: API_BASE + '/countries/state/cities',
                contentType: 'application/json',
                data: JSON.stringify({ country, state }),
                success: function(res){
                    let html = '<option value="">Select City</option>';
                    res.data.forEach(city => {
                        html += `<option value="${city}">${city}</option>`;
                    });
                    $('#cityFilter').html(html);
                }
            });
        }
    });
</script>
@endpush
