@extends('admin.layouts.master')

@section('title', 'Fill Counselling Data - ' . $counselling->counselling_name)

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
            <h4 class="mb-0 fw-bold"><i class="fas fa-pen-nib me-2 text-primary"></i>Fill Counselling Data:
                {{ $counselling->counselling_name }}</h4>
            <p class="text-muted mb-0">Exam: {{ $dynamicExam->name }}. Each tab saves independently.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div id="autosave-status" class="text-muted small">
                <i class="fas fa-check-circle text-success me-1"></i> Ready
            </div>
            <a href="{{ route('admin.dynamic-exams.counsellings.index', $dynamicExam->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">

            {{-- Tab Headers --}}
            <ul class="nav-tabs-custom" id="counsellingDataTabs">
                <li><a class="nav-link active" href="#tab-core" data-tab-id="core">
                        <i class="fas fa-cube me-1"></i> Main Identity
                    </a></li>
                @foreach($counselling->sections as $section)
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
                            <label class="form-label">Counselling Name <span class="text-danger">*</span></label>
                            <input type="text" name="counselling_name" class="form-control" value="{{ $counselling->counselling_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ $counselling->slug }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Counselling Type *</label>
                            <select name="counselling_type" class="form-select select2-single">
                                @foreach(['Centralised', 'State-Level', 'Institute-Level'] as $opt)
                                    <option value="{{ $opt }}" {{ $counselling->counselling_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Counselling Mode</label>
                            <select name="counselling_mode" class="form-select select2-single">
                                @foreach(['Online', 'Offline', 'Hybrid'] as $opt)
                                    <option value="{{ $opt }}" {{ $counselling->counselling_mode == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Conducting Authority Name *</label>
                            <input type="text" name="conducting_authority_name" class="form-control" value="{{ $counselling->conducting_authority_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Conducting Authority Type</label>
                            <select name="conducting_authority_type" class="form-select">
                                @foreach(['Central Government', 'State Government', 'University Body'] as $opt)
                                    <option value="{{ $opt }}" {{ $counselling->conducting_authority_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Official Website</label>
                            <input type="url" name="official_counselling_website" class="form-control" value="{{ $counselling->official_counselling_website }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visibility</label>
                            <select name="visibility" class="form-select">
                                @foreach(['Public', 'Draft', 'Private'] as $opt)
                                    <option value="{{ $opt }}" {{ $counselling->visibility == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Active', 'Upcoming', 'Closed', 'Archived'] as $opt)
                                    <option value="{{ $opt }}" {{ $counselling->status == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="step-footer">
                        <div></div>
                        <button type="button" class="btn btn-success px-4 save-tab-btn" data-tab="core">
                            <i class="fas fa-save me-1"></i> Save Identity
                        </button>
                    </div>
                </div>

                {{-- ===== DYNAMIC SECTION TABS ===== --}}
                @foreach($counselling->sections as $section)
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
                                                    class="form-control editor" @if(!empty($el['required'])) required @endif
                                                     >{!! is_array($val) ? json_encode($val) : $val !!}</textarea>
                                            @elseif($el['inputType'] === 'select')
                                                <select name="{{ $name }}" class="form-select select2-single" @if(!empty($el['required'])) required @endif>
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
                                                                {{ in_array($opt, $vals) ? 'checked' : '' }} @if(!empty($el['required'])) required @endif>
                                                            <label class="form-check-label">{{ $opt }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($el['inputType'] === 'radio')
                                                <div class="d-flex gap-4 flex-wrap mt-1">
                                                    @foreach(explode(',', $el['options'] ?? '') as $opt)
                                                        @php $opt = trim($opt); @endphp
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="{{ $name }}" value="{{ $opt }}" {{ $val == $opt ? 'checked' : '' }} @if(!empty($el['required'])) required @endif>
                                                            <label class="form-check-label">{{ $opt }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($el['inputType'] === 'multi-select')
                                                @php $vals = is_array($val) ? $val : (json_decode($val, true) ?? []); @endphp
                                                <select name="{{ $name }}[]" class="form-select select2-multi" multiple @if(!empty($el['required'])) required @endif>
                                                    @foreach(explode(',', $el['options'] ?? '') as $opt)
                                                        @php $opt = trim($opt); @endphp
                                                        <option value="{{ $opt }}" {{ in_array($opt, $vals) ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
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
                                                    value="{{ is_array($val) ? json_encode($val) : $val }}" @if(!empty($el['required'])) required @endif>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p>No fields in this section. <a
                                        href="{{ route('admin.dynamic-exams.counsellings.edit', [$dynamicExam->id, $counselling->id]) }}">Add fields →</a></p>
                            </div>
                        @endif

                        <div class="step-footer">
                            <div></div>
                            <button type="button" class="btn btn-success px-4 save-tab-btn" data-tab="{{ $section->id }}">
                                <i class="fas fa-save me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2-single').select2({ width: '100%', allowClear: true, placeholder: 'Select...' });
            $('.select2-multi').select2({ width: '100%', allowClear: true, placeholder: 'Select...' });

            if (typeof initializeTinyMCE === 'function') {
                initializeTinyMCE('.editor', 250);
            }

            const AUTOSAVE_URL = "{{ route('admin.dynamic-exams.counsellings.autosave-tab', [$dynamicExam->id, $counselling->id]) }}";
            const CSRF = "{{ csrf_token() }}";

            function setStatus(msg, icon, color) {
                $('#autosave-status').html(`<i class="fas fa-${icon} text-${color} me-1"></i> ${msg}`);
            }

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
                    success: function (res) {
                        setStatus('Saved!', 'check-circle', 'success');
                        $(`#counsellingDataTabs .nav-link[data-tab-id="${tabId}"]`).addClass('saved');
                        if (typeof done === 'function') done();
                    },
                    error: function (xhr) {
                        setStatus('Error saving!', 'exclamation-circle', 'danger');
                        if (typeof done === 'function') done();
                    }
                });
            }

            let activeTabId = 'core';
            let activePane = $('#tab-core');
            let isSaving = false;

            $('#counsellingDataTabs .nav-link').on('click', function (e) {
                e.preventDefault();
                const clickedLink = $(this);
                const targetId = clickedLink.attr('href');
                const nextTabId = clickedLink.data('tab-id').toString();

                if (nextTabId === activeTabId || isSaving) return;

                isSaving = true;
                saveTabData(activePane, activeTabId, function () {
                    isSaving = false;
                    $('#counsellingDataTabs .nav-link').removeClass('active');
                    clickedLink.addClass('active');
                    $('.tab-pane').removeClass('active');
                    $(targetId).addClass('active');
                    activeTabId = nextTabId;
                    activePane = $(targetId);
                });
            });

            $(document).on('click', '.save-tab-btn', function () {
                const btn = $(this);
                const tabId = btn.data('tab').toString();
                const pane = btn.closest('.tab-pane');
                saveTabData(pane, tabId);
            });
        });
    </script>
@endpush
