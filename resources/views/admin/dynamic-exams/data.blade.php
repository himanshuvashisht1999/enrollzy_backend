@extends('admin.layouts.master')

@section('title', 'Fill Exam Data - ' . $dynamicExam->name)

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #dee2e6;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #dee2e6;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        /* Tab Styles */
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            gap: 4px;
        }

        .nav-tabs-custom .nav-link {
            border: 1px solid transparent;
            border-radius: .25rem .25rem 0 0;
            padding: .6rem 1.2rem;
            font-size: 14px;
            color: #0d6efd;
            background: none;
            text-decoration: none;
            display: block;
            transition: all .2s;
        }

        .nav-tabs-custom .nav-link:hover {
            background: #f8f9fa;
        }

        .nav-tabs-custom .nav-link.active {
            color: #212529 !important;
            background-color: #fff !important;
            border-color: #dee2e6 #dee2e6 #fff !important;
            font-weight: 600;
            border-bottom: 2px solid #fff;
            margin-bottom: -2px;
        }

        .nav-tabs-custom .nav-link.saved {
            color: #198754 !important;
        }

        .nav-tabs-custom .nav-link.saved::after {
            content: " ✓";
        }

        #autosave-status {
            font-size: 13px;
            min-width: 150px;
            text-align: right;
        }

        .step-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-pen-nib me-2 text-primary"></i>Fill Exam Data:
                {{ $dynamicExam->name }}</h4>
            <p class="text-muted mb-0">Fill data tab by tab. Each tab saves independently.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div id="autosave-status" class="text-muted small">
                <i class="fas fa-check-circle text-success me-1"></i> Ready
            </div>
            <a href="{{ route('admin.dynamic-exams.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if($dynamicExam->sections->count() === 0 && true)
    @endif

    <div class="card shadow">
        <div class="card-body">

            {{-- Tab Headers --}}
            <ul class="nav-tabs-custom" id="examDataTabs">
                <li><a class="nav-link active" href="#tab-core" data-tab-id="core">
                        <i class="fas fa-cube me-1"></i> Core Exam Identity
                    </a></li>
                @foreach($dynamicExam->sections as $section)
                    <li><a class="nav-link" href="#tab-{{ $section->id }}" data-tab-id="{{ $section->id }}">
                            {{ $section->heading }}
                        </a></li>
                @endforeach
            </ul>

            {{-- Tab Content Panels --}}
            <div class="tab-content">

                {{-- ===== CORE TAB ===== --}}
                <div class="tab-pane active" id="tab-core">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $dynamicExam->name }}"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Short Name</label>
                            <input type="text" name="short_name" class="form-control"
                                value="{{ $dynamicExam->short_name }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exam Type</label>
                            <select name="exam_type" class="form-select select2-single">
                                <option value="">Select Type</option>
                                @foreach(['National', 'State', 'University-Level', 'International', 'School-Level'] as $opt)
                                    <option value="{{ $opt }}" {{ $dynamicExam->exam_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exam Category</label>
                            @php $cats = is_array($dynamicExam->exam_category) ? $dynamicExam->exam_category : []; @endphp
                            <select name="exam_category[]" class="form-select select2-multi" multiple
                                data-placeholder="Select Category">
                                @foreach(['Engineering', 'Medical', 'Management', 'Law', 'School Admission'] as $opt)
                                    <option value="{{ $opt }}" {{ in_array($opt, $cats) ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Conducting Body Type</label>
                            <select name="conducting_body_type" class="form-select">
                                <option value="">Select Body</option>
                                @foreach(['Government', 'Private Body', 'University'] as $opt)
                                    <option value="{{ $opt }}" {{ $dynamicExam->conducting_body_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exam Frequency</label>
                            <select name="exam_frequency" class="form-select">
                                <option value="">Select Frequency</option>
                                @foreach(['Once a Year', 'Twice a Year', 'Multiple Times', 'Other'] as $opt)
                                    <option value="{{ $opt }}" {{ $dynamicExam->exam_frequency == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Conducting Authority Name</label>
                            <input type="text" name="conducting_authority_name" class="form-control"
                                value="{{ $dynamicExam->conducting_authority_name }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Exam Logo</label>
                            @if($dynamicExam->logo)
                                <div class="mb-1"><img src="{{ asset($dynamicExam->logo) }}" height="40"></div>
                            @endif
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cover Image</label>
                            @if($dynamicExam->cover_image)
                                <div class="mb-1"><img src="{{ asset($dynamicExam->cover_image) }}" height="40"></div>
                            @endif
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <div class="form-text text-muted">Image size should not exceed 2MB.</div>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="fw-bold">Ownership (Internal vs External)</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Source Type</label>
                            <select name="exam_source_type" class="form-select">
                                <option value="External" {{ $dynamicExam->exam_source_type == 'External' ? 'selected' : '' }}>External (General)</option>
                                <option value="Internal" {{ $dynamicExam->exam_source_type == 'Internal' ? 'selected' : '' }}>Internal (Owned by Org)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Owning Organisation (If Internal)</label>
                            <select name="owning_organisation_id" class="form-select select2-single">
                                <option value="">Select Organisation</option>
                                @foreach($organisations as $org)
                                    <option value="{{ $org->id }}" {{ $dynamicExam->owning_organisation_id == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label">About Exam</label>
                            <textarea name="about_exam" id="about_exam_editor"
                                class="form-control editor">{!! $dynamicExam->about_exam !!}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label">Official Website</label>
                            <input type="url" name="official_website" class="form-control"
                                value="{{ $dynamicExam->official_website }}">
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label">Visibility</label>
                            <select name="visibility" class="form-select">
                                @foreach(['Public', 'Draft', 'Private'] as $opt)
                                    <option value="{{ $opt }}" {{ $dynamicExam->visibility == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Active', 'Upcoming', 'Archived'] as $opt)
                                    <option value="{{ $opt }}" {{ $dynamicExam->status == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="featured_exam" value="1" {{ $dynamicExam->featured_exam ? 'checked' : '' }}>
                                <label class="form-check-label">Featured Exam</label>
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="has_stages" id="has_stages_check"
                                    value="1" {{ $dynamicExam->has_stages ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_stages_check">Does this exam has stages?</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3" id="stages_wrapper" style="{{ $dynamicExam->has_stages ? '' : 'display:none;' }}">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Exam Stages <span class="text-danger">*</span></label>
                            <select name="selected_stages[]" class="form-select select2-multi" multiple data-placeholder="Choose Stages">
                                @foreach($allStages as $stage)
                                    <option value="{{ $stage->id }}" {{ in_array($stage->id, $dynamicExam->selected_stages ?? []) ? 'selected' : '' }}>{{ $stage->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="step-footer">
                        <div></div>
                        <button type="button" class="btn btn-success px-4 save-tab-btn" data-tab="core" data-has-file="1">
                            <i class="fas fa-save me-1"></i> Save Core Identity
                        </button>
                    </div>
                </div> {{-- end core tab --}}

                {{-- ===== DYNAMIC SECTION TABS ===== --}}
                @foreach($dynamicExam->sections as $section)
                    <div class="tab-pane" id="tab-{{ $section->id }}">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-4">{{ $section->heading }}</h5>
                        @if(!empty($section->content))
                            <div class="row g-4">
                                @foreach($section->content as $el)
                                    @if($el['type'] === 'subheading')
                                        <div class="col-12">
                                            <h6 class="fw-bold mt-3 mb-2 p-2 rounded text-secondary"
                                                style="background:#f8f9fa; border-left:4px solid #4e73df;">
                                                {{ $el['title'] }}
                                            </h6>
                                        </div>
                                    @elseif($el['type'] === 'input')
                                        @php
                                            $val = $el['value'] ?? '';
                                            $name = "data[{$section->id}][{$el['name']}]";
                                            $isFullWidth = ($el['inputType'] === 'textarea');
                                        @endphp
                                        <div class="{{ $isFullWidth ? 'col-12' : 'col-md-6' }}">
                                            <label class="form-label fw-bold">
                                                {{ $el['label'] }}
                                                @if(!empty($el['required'])) <span class="text-danger">*</span> @endif
                                            </label>
                                            @if($el['inputType'] === 'textarea')
                                                <textarea name="{{ $name }}"
                                                    class="form-control editor">{!! is_array($val) ? json_encode($val) : $val !!}</textarea>
                                            @elseif($el['inputType'] === 'select')
                                                <select name="{{ $name }}" class="form-select select2-single">
                                                    <option value="">-- Select --</option>
                                                    @foreach(explode(',', $el['options'] ?? '') as $opt)
                                                        @php $opt = trim($opt); @endphp
                                                        <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($el['inputType'] === 'checkbox')
                                                @php $vals = is_array($val) ? $val : (json_decode($val, true) ?? []); @endphp
                                                <div class="d-flex gap-4 flex-wrap mt-1">
                                                    @foreach(explode(',', $el['options'] ?? '') as $opt)
                                                        @php $opt = trim($opt); @endphp
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="{{ $name }}[]" value="{{ $opt }}"
                                                                {{ in_array($opt, $vals) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $opt }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($el['inputType'] === 'radio')
                                                <div class="d-flex gap-4 flex-wrap mt-1">
                                                    @foreach(explode(',', $el['options'] ?? '') as $opt)
                                                        @php $opt = trim($opt); @endphp
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="{{ $name }}" value="{{ $opt }}" {{ $val == $opt ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $opt }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($el['inputType'] === 'file')
                                                <input type="file" name="{{ $name }}" class="form-control">
                                                @if($val && is_string($val))
                                                    <div class="mt-1 text-primary">
                                                        <a href="{{ asset($val) }}" target="_blank">
                                                            <i class="fas fa-paperclip"></i> Current file
                                                        </a>
                                                    </div>
                                                    <input type="hidden" name="data[{{ $section->id }}][old_{{ $el['name'] }}]" value="{{ $val }}">
                                                @endif
                                            @else
                                                <input type="{{ $el['inputType'] }}" name="{{ $name }}" class="form-control"
                                                    value="{{ is_array($val) ? json_encode($val) : $val }}">
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p>No fields in this section. <a
                                        href="{{ route('admin.dynamic-exams.edit', $dynamicExam->id) }}">Add fields →</a></p>
                            </div>
                        @endif

                        {{-- Footer --}}
                        <div class="step-footer">
                            <div></div>
                            <button type="button" class="btn btn-success px-4 save-tab-btn" data-tab="{{ $section->id }}"
                                data-section-id="{{ $section->id }}">
                                <i class="fas fa-save me-1"></i> Save "{{ $section->heading }}"
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>{{-- end tab-content --}}
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {

            // ── Select2 init ──
            $('.select2-single').select2({ width: '100%', allowClear: true, placeholder: 'Select...' });
            $('.select2-multi').select2({
                width: '100%', allowClear: true,
                placeholder: function () { return $(this).data('placeholder') || 'Select...'; }
            });

            // ── Stages Toggle ──
            $(document).on('change', '#has_stages_check', function() {
                $('#stages_wrapper').toggle(this.checked);
            });

            // ── TinyMCE init ──
            if (typeof initializeTinyMCE === 'function') {
                initializeTinyMCE('.editor', 250);
            }

            // ── Autosave URL + CSRF ──
            const AUTOSAVE_URL = "{{ route('admin.dynamic-exams.autosave-tab', $dynamicExam->id) }}";
            const CSRF = "{{ csrf_token() }}";

            function setStatus(msg, icon, color) {
                $('#autosave-status').html(`<i class="fas fa-${icon} text-${color} me-1"></i> ${msg}`);
            }

            /**
             * Collect FormData from a given tab pane and save via AJAX.
             * @param {jQuery} tabPane  - the .tab-pane element
             * @param {string} tabId    - 'core' or section numeric id
             * @param {Function} done   - optional callback on success
             */
            function saveTabData(tabPane, tabId, done) {
                setStatus('Saving...', 'spinner fa-spin', 'secondary');

                const fd = new FormData();
                fd.append('_token', CSRF);
                fd.append('_tab', tabId);

                tabPane.find('[name]').each(function () {
                    const el = $(this);
                    const name = el.attr('name');
                    const type = el.attr('type');

                    if (type === 'checkbox') {
                        if (el.is(':checked')) fd.append(name, el.val());
                    } else if (type === 'radio') {
                        if (el.is(':checked')) fd.append(name, el.val());
                    } else if (type === 'file') {
                        if (el[0].files.length > 0) fd.append(name, el[0].files[0]);
                    } else if (el.is('select[multiple]')) {
                        const vals = el.val() || [];
                        vals.forEach(v => fd.append(name, v));
                    } else if (el.hasClass('editor')) {
                        const editor = tinymce ? tinymce.get(el.attr('id')) : null;
                        fd.append(name, editor ? editor.getContent() : el.val());
                    } else {
                        fd.append(name, el.val() || '');
                    }
                });

                $.ajax({
                    url: AUTOSAVE_URL,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function () {
                        setStatus('Saved!', 'check-circle', 'success');
                        $(`#examDataTabs .nav-link[data-tab-id="${tabId}"]`).addClass('saved');

                        if (typeof done === 'function') done();
                    },
                    error: function (xhr) {
                        setStatus('Error saving!', 'exclamation-circle', 'danger');
                        console.error(xhr.responseText);
                        if (typeof done === 'function') done(); // still switch tab on error
                    }
                });
            }

            // ── Tab Switching with Auto-save ──
            let activeTabId = 'core';
            let activePane = $('#tab-core');
            let isSaving = false;

            $('#examDataTabs .nav-link').on('click', function (e) {
                e.preventDefault();

                const clickedLink = $(this);
                const targetId = clickedLink.attr('href');   // e.g. '#tab-core' or '#tab-5'
                const nextTabId = clickedLink.data('tab-id').toString();

                // Don't re-save if clicking the already-active tab
                if (nextTabId === activeTabId) return;
                if (isSaving) return;

                isSaving = true;

                // Save the currently active tab first, then switch
                saveTabData(activePane, activeTabId, function () {
                    isSaving = false;

                    // Switch tab UI
                    $('#examDataTabs .nav-link').removeClass('active');
                    clickedLink.addClass('active');
                    $('.tab-pane').removeClass('active');
                    $(targetId).addClass('active');

                    // Update tracking vars
                    activeTabId = nextTabId;
                    activePane = $(targetId);
                });
            });

            // ── Manual "Save Tab" button click ──
            $(document).on('click', '.save-tab-btn', function () {
                const btn = $(this);
                const tabId = btn.data('tab').toString();
                const pane = btn.closest('.tab-pane');
                const originalHtml = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ' + originalHtml);
                saveTabData(pane, tabId, function () {
                    btn.prop('disabled', false).html(originalHtml);
                });
            });

        });
    </script>
@endpush