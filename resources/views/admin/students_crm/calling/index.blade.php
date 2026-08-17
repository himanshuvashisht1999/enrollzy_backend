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
                    <select name="session_id" class="form-select rounded-3" id="sessionFilter">
                        <option value="">Select Session</option>
                        @if(isset($sessions))
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        @endif
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
<div class="modal fade" id="callModal">
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
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" class="form-control rounded-3" name="name" id="user_name" readonly>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">Call Status <span class="text-danger">*</span></label>
                            <select name="status_id" class="form-select rounded-3" id="status_id" required>
                                <option value="" selected disabled>Select</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" data-action="{{ $status->calling_action_id }}" data-more-details="{{ $status->is_more_details }}" data-date-require="{{ $status->date_require }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- More Details Container -->
                        <div id="more-details-container" class="col-12" style="display:none; margin-top: 0px;">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label small fw-bold">Program Level</label>
                                    <select name="program_level_id" id="program_level_id" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Program Level</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @if(isset($program_levels))
                                            @foreach($program_levels as $pl)
                                                <option value="{{ $pl->id }}">{{ $pl->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-4" id="school_type_container" style="display:none;">
                                    <label class="form-label small fw-bold">School Type</label>
                                    <select name="school_type" id="school_type" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type School Type</option>
                                        @if(isset($school_types))
                                            @foreach($school_types as $st)
                                                <option value="{{ $st->id }}">{{ $st->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-4" id="course_container">
                                    <label class="form-label small fw-bold" id="course_label">Course</label>
                                    <select name="course_input" id="course_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Course</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4" id="course_type_container">
                                    <label class="form-label small fw-bold">Program Mode</label>
                                    <select name="course_type" id="course_type" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type Program Mode</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @if(isset($program_types))
                                            @foreach($program_types as $pt)
                                                <option value="{{ $pt->title }}" data-db-id="{{ $pt->id }}">{{ $pt->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-6" id="university_container">
                                    <label class="form-label small fw-bold" id="university_label">University / Organization</label>
                                    <select name="university_input" id="university_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select or Type University</option>
                                        <option value="Not decided yet">Not decided yet</option>
                                        @foreach($universities as $uni)
                                            <option value="{{ $uni->id }}" data-type-id="{{ $uni->organisation_type_id }}">{{ $uni->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label small fw-bold">Session</label>
                                    <select name="session" id="session_input" class="form-select rounded-3 custom-select2">
                                        <option value="">Select Session</option>
                                        @if(isset($sessions))
                                            @foreach($sessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6" id="date-field" style="display:none;">
                            <label class="form-label small fw-bold">Reminder Date</label>
                            <input type="date" name="next_call_date" class="form-control rounded-3" id="call_date">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label small fw-bold">Action Taken</label>
                            <select name="action_id" id="action_id" class="form-select rounded-3">
                                <option value="">Select Action</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action->id }}">{{ $action->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Video Meeting Container -->
                        <div id="video-meeting-container" class="col-12" style="display:none; margin-top: 0px;">
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Meeting Date</label>
                                    <input type="date" name="meeting_date" id="meeting_date" class="form-control rounded-3">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Time Slot</label>
                                    <input type="time" name="time_slot" id="time_slot" class="form-control rounded-3">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Google Meeting Link</label>
                                    <input type="url" name="meeting_link" id="meeting_link" class="form-control rounded-3" placeholder="https://meet.google.com/...">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label small fw-bold">Assign Lead to Staff</label>
                                    <select name="assign_to_staff_id" id="assign_to_staff_id" class="form-select rounded-3 custom-select2">
                                        <option value="">Select Staff</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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
            let group = '{{ request('group', 1) }}';
            let baseUrl = '{{ route('admin.students-crm.calling-module.restart') }}';
            
            if(!cat) {
                Swal.fire('Warning', 'Please select a category first before clicking Re-Start.', 'warning');
                return;
            }
            
            var params = new URLSearchParams();
            params.set('group', group);
            params.set('category', cat);
            
            window.location.href = baseUrl + '?' + params.toString();
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
                        $('#university_input').val(null).trigger('change');
                        $('#course_input').val(null).trigger('change');
                        $('#assign_to_staff_id').val(null).trigger('change');
                        $('#more-details-container').hide();
                        $('#video-meeting-container').hide();
                        $('#date-field').hide();
                        $('#call_date').prop('required', false);
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
            let selected = $(this).find('option:selected');
            let actionId = selected.data('action');
            let moreDetails = selected.data('more-details');
            let dateRequire = selected.data('date-require');
            
            if(actionId) {
                $('#action_id').val(actionId).trigger('change');
            } else {
                $('#action_id').val('').trigger('change');
            }
            
            if(moreDetails === 'yes') {
                $('#more-details-container').show();
            } else {
                $('#more-details-container').hide();
            }
            
            if(dateRequire === 'yes') {
                $('#date-field').show();
                $('#call_date').prop('required', true);
            } else {
                $('#date-field').hide();
                $('#call_date').prop('required', false);
            }
        });
        
        $('#university_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type University",
            allowClear: true,
            width: '100%'
        });

        $('#course_input').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Program Level",
            allowClear: true,
            width: '100%'
        });

        $('#course_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type Course Type",
            allowClear: true,
            width: '100%'
        });

        $('#assign_to_staff_id').select2({
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select Staff",
            allowClear: true,
            width: '100%'
        });

        $('#action_id').on('change', function() {
            let actionText = $(this).find('option:selected').text().trim().toLowerCase();
            if(actionText === 'arrange video meeting') {
                $('#video-meeting-container').show();
            } else {
                $('#video-meeting-container').hide();
            }
        });
        
        let allUniversities = [];
        let allCourseTypes = [];
        let allCourses = [];
        let courseProgramTypes = @json($course_program_types ?? []);
        
        $(document).ready(function() {
            $('#university_input option').each(function() {
                allUniversities.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    typeId: $(this).data('type-id')
                });
            });
            
            $('#course_type option').each(function() {
                allCourseTypes.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    dbId: $(this).data('db-id')
                });
            });

            $('#course_input option').each(function() {
                allCourses.push({
                    id: $(this).val(),
                    text: $(this).text()
                });
            });
        });

        $('#school_type').select2({
            tags: true,
            dropdownParent: $('#callModal .modal-content'),
            placeholder: "Select or Type School Type",
            allowClear: true,
            width: '100%'
        });

        $('#program_level_id').on('change', function() {
            let levelId = $(this).val();
            let selectedText = $(this).find('option:selected').text().trim().toLowerCase();
            
            let universitySelect = $('#university_input');
            universitySelect.empty();

            if (selectedText === 'school') {
                $('#school_type_container').show();
                $('#course_label').text('Choose Class');
                $('#course_type_container').hide();
                $('#university_label').text('School Name');
                
                allUniversities.forEach(function(u) {
                    if (!u.id || u.id === 'Not decided yet' || u.typeId == 4) {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
            } else if (selectedText === 'competetive coaching' || selectedText === 'competitive coaching') {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('Choose institute');
                
                allUniversities.forEach(function(u) {
                    if (!u.id || u.id === 'Not decided yet' || u.typeId == 3) {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
            } else {
                $('#school_type_container').hide();
                $('#course_label').text('Course');
                $('#course_type_container').show();
                $('#university_label').text('University / Organization');
                
                allUniversities.forEach(function(u) {
                    if (u.typeId != 4 || !u.id || u.id === 'Not decided yet') {
                        let option = new Option(u.text, u.id, false, false);
                        $(option).attr('data-type-id', u.typeId);
                        universitySelect.append(option);
                    }
                });
            }
            universitySelect.trigger('change');

            let courseSelect = $('#course_input');
            courseSelect.html('<option value="">Loading...</option>').trigger('change');
            
            $.ajax({
                url: '{{ route("admin.students-crm.calling-module.get-courses") }}',
                type: 'GET',
                data: { program_level_id: levelId },
                success: function(res) {
                    let html = '<option value="">Select or Type Course</option>';
                    html += '<option value="Not decided yet">Not decided yet</option>';
                    if(res && res.length > 0) {
                        res.forEach(c => {
                            html += `<option value="${c.id}">${c.name}</option>`;
                        });
                    } else {
                        allCourses.forEach(function(c) {
                            if (c.id && c.id !== 'Not decided yet') {
                                html += `<option value="${c.id}">${c.text}</option>`;
                            }
                        });
                    }
                    courseSelect.html(html).trigger('change');
                },
                error: function() {
                    courseSelect.html('<option value="">Select or Type Course</option><option value="Not decided yet">Not decided yet</option>').trigger('change');
                }
            });
        });

        $('#course_input').on('change', function() {
            let courseId = $(this).val();
            let programLevelText = $('#program_level_id').find('option:selected').text().trim().toLowerCase();
            
            let courseTypeSelect = $('#course_type');
            
            if (programLevelText === 'competetive coaching' || programLevelText === 'competitive coaching') {
                courseTypeSelect.empty();
                
                let option1 = new Option('Select or Type Program Mode', '', false, false);
                let option2 = new Option('Not decided yet', 'Not decided yet', false, false);
                courseTypeSelect.append(option1).append(option2);

                if (courseId && courseId !== 'Not decided yet') {
                    let allowedTypeIds = courseProgramTypes
                        .filter(cpt => cpt.course_id == courseId)
                        .map(cpt => parseInt(cpt.program_type_id));
                        
                    allCourseTypes.forEach(function(ct) {
                        if (ct.id && ct.id !== 'Not decided yet' && allowedTypeIds.includes(parseInt(ct.dbId))) {
                            let option = new Option(ct.text, ct.id, false, false);
                            $(option).attr('data-db-id', ct.dbId);
                            courseTypeSelect.append(option);
                        }
                    });
                }
                courseTypeSelect.trigger('change');
            } else {
                // Restore all course types if not coaching
                courseTypeSelect.empty();
                allCourseTypes.forEach(function(ct) {
                    let option = new Option(ct.text, ct.id, false, false);
                    if (ct.dbId) $(option).attr('data-db-id', ct.dbId);
                    courseTypeSelect.append(option);
                });
                courseTypeSelect.trigger('change');
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
