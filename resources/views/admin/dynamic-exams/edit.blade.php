@extends('admin.layouts.master')

@section('title', 'Schema Builder')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --app-primary: #6366f1;
            --app-primary-hover: #4f46e5;
            --app-bg: #f9fafb;
            --app-border: #e5e7eb;
            --app-text-main: #111827;
            --app-text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: var(--app-text-main);
        }

        .main-builder-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
            min-height: calc(100vh - 120px);
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--app-border);
        }

        /* Sidebar Area */
        .builder-nav {
            background: #fff;
            border-right: 1px solid var(--app-border);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--app-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            padding-left: 12px;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--app-text-main);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 4px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-btn:hover {
            background: #f3f4f6;
        }

        .nav-btn.active {
            background: #e0e7ff;
            color: var(--app-primary);
        }

        .nav-btn i {
            margin-right: 10px;
            font-size: 16px;
            opacity: 0.7;
        }

        /* Content Area */
        .builder-content {
            padding: 32px 48px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .section-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--app-border);
        }

        .section-title-input {
            background: transparent;
            border: 1px solid transparent;
            font-size: 24px;
            font-weight: 700;
            color: var(--app-dark);
            width: 100%;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .section-title-input:not([readonly]) {
            background: #fff;
            border-color: var(--app-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .section-title-input[readonly] {
            cursor: default;
            outline: none;
        }

        /* Group Label (Subheading) Style */
        .group-label-input {
            background: transparent;
            border: none;
            font-size: 18px;
            font-weight: 700;
            color: #fbbf24;
            width: 100%;
            outline: none;
        }

        /* Field Cards */
        .field-row {
            background: #fff;
            border: 1px solid var(--app-border);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            transition: border-color 0.2s;
        }

        .field-row:hover {
            border-color: var(--app-primary);
        }

        .field-drag-handle {
            color: #d1d5db;
            cursor: grab;
            margin-right: 12px;
        }

        .subheading-row {
            background: #fdfdfd;
            border-left: 4px solid #fbbf24;
        }

        .remove-action {
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.2s;
        }

        .remove-action:hover {
            color: #ef4444;
        }

        /* Form Controls */
        .label-base {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .input-base {
            border: 1px solid var(--app-border);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            width: 100%;
            transition: border-color 0.2s;
        }

        .input-base:focus {
            border-color: var(--app-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Action Buttons */
        .btn-add-wrapper {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding: 24px;
            background: #f9fafb;
            border: 1px dashed var(--app-border);
            border-radius: 10px;
            justify-content: center;
        }

        .btn-action {
            background: #fff;
            border: 1px solid var(--app-border);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .btn-primary-app {
            background: var(--app-primary);
            color: #fff;
            border: none;
        }

        .btn-primary-app:hover {
            background: var(--app-primary-hover);
            color: #fff;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        /* Core Form Style */
        .core-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        /* Clean Switch */
        .switch-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .switch-ui {
            width: 40px;
            height: 20px;
            background: #d1d5db;
            border-radius: 20px;
            position: relative;
            cursor: pointer;
            transition: 0.2s;
        }

        input:checked+.switch-ui {
            background: var(--app-primary);
        }

        .switch-ui:after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: 0.2s;
        }

        input:checked+.switch-ui:after {
            left: 22px;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h4 fw-bold mb-0">Build Exam Structure</h1>
            <p class="text-muted small mb-0">{{ $dynamicExam->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dynamic-exams.index') }}" class="btn btn-sm btn-outline-secondary px-3">Discard</a>
            <button type="button" class="btn btn-sm btn-primary-app px-4" id="mainSaveBtn"
                onclick="saveActiveTab()">Save</button>
        </div>
    </div>

    <form action="{{ route('admin.dynamic-exams.update', $dynamicExam->id) }}" method="POST" id="mainExamForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="sections" id="sectionsDataPayload">

        <div class="main-builder-wrapper">
            <!-- Sidebar -->
            <div class="builder-nav">
                <div class="nav-label">Core Config</div>
                <div class="nav-btn active" id="coreTabBtn" onclick="builder.showCore()">
                    <i class="fas fa-cog"></i> General Settings
                </div>

                <div class="nav-label mt-4">Form Structure</div>
                <div id="sectionsList">
                    <!-- Nav items added here -->
                </div>

                <button type="button" class="nav-btn w-100 text-primary bg-light border-0 py-2 mt-2"
                    onclick="builder.addSection()">
                    <i class="fas fa-plus"></i> New Section
                </button>
            </div>

            <!-- Main Workspace -->
            <div style="flex: 1; overflow-y: auto; max-height: 80vh;">
                <div class="builder-content">

                    <div id="placeholder" class="text-center py-5" style="display:none;">
                        <i class="fas fa-mouse-pointer text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted">Select a section to start building</h5>
                    </div>

                    <!-- Core Form -->
                    <div id="coreExamForm" style="display:block;">
                        <div class="section-header">
                            <h2 class="fw-bold mb-1">General Settings</h2>
                            <p class="text-muted small">Configure global identifiers and core behavior</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-base">Exam Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="exam_name_main" class="input-base"
                                    value="{{ $dynamicExam->name }}" required onkeyup="builder.save()">
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Short Name</label>
                                <input type="text" name="short_name" class="input-base"
                                    value="{{ $dynamicExam->short_name }}" onchange="builder.save()">
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Exam Type</label>
                                <select name="exam_type" class="input-base select2-single" onchange="builder.save()">
                                    <option value="">Select Type</option>
                                    @foreach(['National', 'State', 'University-Level', 'International', 'School-Level'] as $opt)
                                        <option value="{{ $opt }}" {{ $dynamicExam->exam_type == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Exam Category</label>
                                <select name="exam_category[]" class="input-base select2-multi" multiple
                                    onchange="builder.save()">
                                    @php
                                        $selectedCats = $dynamicExam->exam_category;
                                        if (is_string($selectedCats)) {
                                            $selectedCats = json_decode($selectedCats, true) ?? [];
                                        }
                                        $selectedCats = is_array($selectedCats) ? $selectedCats : [];
                                    @endphp
                                    @foreach(['Engineering', 'Medical', 'Management', 'Law', 'School Admission'] as $opt)
                                        <option value="{{ $opt }}"
                                            {{ in_array($opt, $selectedCats) ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Conducting Body Type</label>
                                <select name="conducting_body_type" class="input-base select2-single" onchange="builder.save()">
                                    <option value="">Select Body</option>
                                    @foreach(['Government', 'Private Body', 'University'] as $opt)
                                        <option value="{{ $opt }}"
                                            {{ $dynamicExam->conducting_body_type == $opt ? 'selected' : '' }}>{{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Exam Frequency</label>
                                <select name="exam_frequency" class="input-base select2-single" onchange="builder.save()">
                                    <option value="">Select Frequency</option>
                                    @foreach(['Once a Year', 'Twice a Year', 'Multiple Times', 'Other'] as $opt)
                                        <option value="{{ $opt }}"
                                            {{ $dynamicExam->exam_frequency == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Conducting Authority Name</label>
                                <input type="text" name="conducting_authority_name" class="input-base"
                                    value="{{ $dynamicExam->conducting_authority_name }}" onchange="builder.save()">
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Exam Logo</label>
                                <div id="logo_preview">
                                    @if($dynamicExam->logo)
                                        <div class="mb-2"><img src="{{ asset($dynamicExam->logo) }}" height="40"
                                                class="rounded border"></div>
                                    @endif
                                </div>
                                <input type="file" name="logo" class="input-base" accept="image/*"
                                    onchange="previewImage(this, 'logo_preview')">
                                <div class="small text-muted mt-1">Image size should not exceed 2MB.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Cover Image</label>
                                <div id="cover_preview">
                                    @if($dynamicExam->cover_image)
                                        <div class="mb-2"><img src="{{ asset($dynamicExam->cover_image) }}" height="40"
                                                class="rounded border"></div>
                                    @endif
                                </div>
                                <input type="file" name="cover_image" class="input-base" accept="image/*"
                                    onchange="previewImage(this, 'cover_preview')">
                                <div class="small text-muted mt-1">Image size should not exceed 2MB.</div>
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <h6 class="fw-bold">Ownership (Internal vs External)</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="label-base">Source Type</label>
                                <select name="exam_source_type" class="input-base select2-single" id="exam_source_type"
                                    onchange="builder.save()">
                                    <option value="External" {{ $dynamicExam->exam_source_type == 'External' ? 'selected' : '' }}>
                                        External (General)</option>
                                    <option value="Internal" {{ $dynamicExam->exam_source_type == 'Internal' ? 'selected' : '' }}>
                                        Internal (Owned by Org)</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="owningOrgWrapper">
                                <label class="label-base">Owning Organisation (If Internal)</label>
                                <select name="owning_organisation_id" class="input-base select2-single"
                                    onchange="builder.save()">
                                    <option value="">Select Organisation</option>
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ $dynamicExam->owning_organisation_id == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="label-base">About Exam</label>
                                <textarea name="about_exam" id="about_exam_editor" class="input-base editor"
                                    rows="4">{{ $dynamicExam->about_exam }}</textarea>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="label-base">Official Website</label>
                                <input type="url" name="official_website" class="input-base"
                                    value="{{ $dynamicExam->official_website }}" placeholder="https://..."
                                    onchange="builder.save()">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="label-base">Visibility</label>
                                <select name="visibility" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Public', 'Draft', 'Private'] as $v)
                                        <option value="{{ $v }}" {{ $dynamicExam->visibility == $v ? 'selected' : '' }}>{{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="label-base">Status</label>
                                <select name="status" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Active', 'Upcoming', 'Archived'] as $s)
                                        <option value="{{ $s }}" {{ $dynamicExam->status == $s ? 'selected' : '' }}>{{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch ps-5">
                                    <input class="form-check-input" type="checkbox" name="featured_exam" value="1" id="featCheck"
                                        {{ $dynamicExam->featured_exam ? 'checked' : '' }} onchange="builder.save()">
                                    <label class="form-check-label fw-semibold small text-muted" for="featCheck">Featured
                                        Exam</label>
                                </div>
                            </div>

                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch ps-5">
                                    <input class="form-check-input" type="checkbox" name="has_stages" value="1" id="stagesCheck"
                                        {{ $dynamicExam->has_stages ? 'checked' : '' }} onchange="builder.save()">
                                    <label class="form-check-label fw-semibold small text-muted" for="stagesCheck">Does this
                                        exam has stages?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="mt-5 p-3 rounded bg-light border">
                                <div class="d-flex gap-3">
                                    <i class="fas fa-info-circle text-muted mt-1"></i>
                                    <div class="small text-muted">
                                        This builder defines the <strong>schema</strong> (headings and inputs). Actual descriptive
                                        data like logos, categories, and full descriptions are filled in the data entry view after
                                        saving this structure.
                                    </div>
                                </div>
                            </div> -->
                </div>

                <!-- Section Editor -->
                <div id="activeSectionEditor" style="display:none;">
                    <div class="section-header d-flex justify-content-between align-items-end">
                        <div style="flex: 1" class="m-2">
                            <label class="label-base d-flex align-items-center gap-2">
                                Section Name <i class="fas fa-edit small text-muted cursor-pointer"
                                    onclick="builder.enableHeadingEdit()"></i>
                            </label>
                            <input type="text" id="activeSectionHeading" class="section-title-input" readonly
                                placeholder="Untitled Section" onblur="this.readOnly = true"
                                onkeyup="builder.updateActiveHeading(this.value)">
                        </div>
                        <button type="button" class="btn btn-link text-danger btn-sm p-0 mb-1"
                            onclick="builder.deleteActiveSection()">Delete Section</button>
                    </div>

                    <div id="elementsContainer">
                        <!-- Elements rendered here -->
                    </div>

                    <div class="btn-add-wrapper">
                        <button type="button" class="btn-action" onclick="builder.addElement('subheading')">
                            <i class="fas fa-heading text-warning"></i> Add Group Label
                        </button>
                        <button type="button" class="btn-action" onclick="builder.addElement('input')">
                            <i class="fas fa-plus-circle text-primary"></i> Add Input Field
                        </button>
                    </div>
                </div>

            </div>
        </div>
        </div>
    </form>
@endsection

@push('js')
    <script>
        const builder = {
            sections: {!! json_encode($dynamicExam->sections->map(function ($s) {
        return [
            'id' => $s->id,
            'temp_id' => $s->id ? 'sec_' . $s->id : 'new_' . uniqid(),
            'heading' => $s->heading,
            'content' => is_string($s->content) ? json_decode($s->content, true) : ($s->content ?? [])
        ];
    })->values()) !!},
            activeSectionId: null,

            init() {
                this.renderSidebar();
                
                // Try to restore last active section
                const lastActive = sessionStorage.getItem('activeSection_' + {{ $dynamicExam->id }});
                if (lastActive === 'core') {
                    this.showCore();
                } else if (lastActive && this.sections.find(s => s.temp_id === lastActive)) {
                    this.setActive(lastActive);
                } else if (this.sections.length > 0) {
                    this.setActive(this.sections[0].temp_id);
                } else {
                    this.showCore();
                }
            },

            generateId() {
                return 'id_' + Math.random().toString(36).substr(2, 9);
            },

            slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '_')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '_')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            },

            addSection() {
                const newSec = {
                    id: null,
                    temp_id: this.generateId(),
                    heading: 'New Section ' + (this.sections.length + 1),
                    content: []
                };
                this.sections.push(newSec);
                this.renderSidebar();
                this.setActive(newSec.temp_id);
                this.save();
            },

            deleteSection(temp_id) {
                if (!confirm('Are you sure you want to delete this section?')) return;
                this.sections = this.sections.filter(s => s.temp_id !== temp_id);
                if (this.activeSectionId === temp_id) {
                    this.activeSectionId = null;
                    this.showCore();
                }
                this.renderSidebar();
                this.save();
            },

            deleteActiveSection() {
                this.deleteSection(this.activeSectionId);
            },

            showCore() {
                this.activeSectionId = null;
                sessionStorage.setItem('activeSection_' + {{ $dynamicExam->id }}, 'core');
                const coreBtn = document.getElementById('coreTabBtn');
                if (coreBtn) {
                    coreBtn.classList.add('active');
                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        if (btn.id !== 'coreTabBtn') btn.classList.remove('active');
                    });
                }

                const coreForm = document.getElementById('coreExamForm');
                if (coreForm) coreForm.style.display = 'block';

                document.getElementById('activeSectionEditor').style.display = 'none';
                if (document.getElementById('placeholder')) {
                    document.getElementById('placeholder').style.display = 'none';
                }
            },

            setActive(id) {
                this.activeSectionId = id;
                sessionStorage.setItem('activeSection_' + {{ $dynamicExam->id }}, id);
                const coreBtn = document.getElementById('coreTabBtn');
                if (coreBtn) coreBtn.classList.remove('active');

                const coreForm = document.getElementById('coreExamForm');
                if (coreForm) coreForm.style.display = 'none';

                this.renderSidebar();
                this.renderEditor();

                // Ensure it's readonly when switching
                const heading = document.getElementById('activeSectionHeading');
                if (heading) heading.readOnly = true;
            },

            enableHeadingEdit() {
                const heading = document.getElementById('activeSectionHeading');
                if (heading) {
                    heading.readOnly = false;
                    heading.focus();
                }
            },

            updateActiveHeading(val) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (sec) {
                    sec.heading = val;
                    this.renderSidebar();
                    if (this.saveTimeout) clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => this.save(), 500);
                }
            },

            save(isManual = false) {
                const btn = document.getElementById('mainSaveBtn');
                const origText = btn ? btn.innerHTML : 'Save';
                if (isManual && btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                }

                const form = document.getElementById('mainExamForm');
                const formData = new FormData(form);
                formData.set('sections', JSON.stringify(this.sections));
                formData.set('_method', 'PATCH');

                fetch(`{{ route('admin.dynamic-exams.update', $dynamicExam->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        if (isManual) toastr.success('Success', res.message || 'Saved successfully');
                        console.log('Saved successfully');
                    } else {
                        if (isManual) toastr.error('Error', res.message || 'Save failed');
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    if (isManual) {
                        // If we get an error, it might be because the server returned HTML (redirect)
                        // but since we updated the controller, this should happen less often.
                        toastr.error('Error', 'Save failed. Please check your connection.');
                    }
                })
                .finally(() => {
                    if (isManual && btn) {
                        btn.disabled = false;
                        btn.innerHTML = origText;
                    }
                });
            },

            renderSidebar() {
                const list = document.getElementById('sectionsList');
                list.innerHTML = '';
                this.sections.forEach(sec => {
                    const div = document.createElement('div');
                    div.className = 'nav-btn' + (this.activeSectionId === sec.temp_id ? ' active' : '');
                    div.setAttribute('data-id', sec.temp_id);
                    div.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="text-truncate" style="max-width: 140px;">
                                    <i class="fas fa-bars me-2 small opacity-50"></i> ${sec.heading || 'Untitled'}
                                </div>
                                <div class="nav-actions">
                                    <i class="fas fa-edit small p-1 cursor-pointer" onclick="event.stopPropagation(); builder.setActive('${sec.temp_id}'); builder.enableHeadingEdit()"></i>
                                    <i class="fas fa-trash-alt small p-1 text-danger cursor-pointer" onclick="event.stopPropagation(); builder.deleteSection('${sec.temp_id}')"></i>
                                </div>
                            </div>
                        `;
                    div.onclick = () => this.setActive(sec.temp_id);
                    list.appendChild(div);
                });
            },

            renderEditor() {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (!sec) return;

                document.getElementById('placeholder').style.display = 'none';
                document.getElementById('activeSectionEditor').style.display = 'block';

                document.getElementById('activeSectionHeading').value = sec.heading;

                const container = document.getElementById('elementsContainer');
                container.innerHTML = '';

                sec.content.forEach((el, index) => {
                    if (el.type === 'subheading') {
                        container.appendChild(this.createSubheadingNode(sec, el, index));
                    } else if (el.type === 'input') {
                        container.appendChild(this.createInputNode(sec, el, index));
                    }
                });
            },

            addElement(type) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (!sec) return;

                if (type === 'subheading') {
                    sec.content.push({ type: 'subheading', title: 'Group Label' });
                } else {
                    sec.content.push({ type: 'input', inputType: 'text', label: 'Field Label', name: 'field_' + Date.now(), required: false, options: '' });
                }
                this.renderEditor();
            },

            removeElement(index) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (sec) {
                    sec.content.splice(index, 1);
                    this.renderEditor();
                }
            },

            createSubheadingNode(sec, el, index) {
                const div = document.createElement('div');
                div.className = 'field-row subheading-row d-flex align-items-center';
                div.innerHTML = `
                                <div class="field-drag-handle"><i class="fas fa-grip-vertical"></i></div>
                                <div style="flex: 1">
                                    <input type="text" class="group-label-input" value="${el.title}" oninput="builder.updateElement(${index}, 'title', this.value)">
                                </div>
                                <div class="remove-action" onclick="builder.removeElement(${index})"><i class="fas fa-trash"></i></div>
                            `;
                return div;
            },

            createInputNode(sec, el, index) {
                const div = document.createElement('div');
                div.className = 'field-row';
                div.innerHTML = `
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="field-drag-handle"><i class="fas fa-grip-vertical"></i></div>
                                        <span class="fw-bold small text-muted text-uppercase">Field #${index + 1}</span>
                                    </div>
                                    <div class="remove-action" onclick="builder.removeElement(${index})"><i class="fas fa-trash"></i></div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="label-base">Field Label</label>
                                        <input type="text" class="input-base" value="${el.label}" placeholder="e.g. Email Address" oninput="builder.autoSlugAndSave(${index}, this.value)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="label-base">Key (API Name)</label>
                                        <input type="text" id="name_input_${index}" class="input-base text-primary bg-light" style="font-family: monospace" value="${el.name}" oninput="this.value = builder.slugify(this.value); builder.updateElement(${index}, 'name', this.value)">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="label-base">Input Type</label>
                                        <select class="input-base" onchange="builder.updateElement(${index}, 'inputType', this.value)">
                                            <option value="text" ${el.inputType == 'text' ? 'selected' : ''}>Text Line</option>
                                            <option value="textarea" ${el.inputType == 'textarea' ? 'selected' : ''}>Rich Editor</option>
                                            <option value="number" ${el.inputType == 'number' ? 'selected' : ''}>Number</option>
                                            <option value="date" ${el.inputType == 'date' ? 'selected' : ''}>Date</option>
                                            <option value="file" ${el.inputType == 'file' ? 'selected' : ''}>File</option>
                                            <option value="select" ${el.inputType == 'select' ? 'selected' : ''}>Select</option>
                                            <option value="radio" ${el.inputType == 'radio' ? 'selected' : ''}>Radio</option>
                                            <option value="checkbox" ${el.inputType == 'checkbox' ? 'selected' : ''}>Checkbox</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12" id="options_wrapper_${index}" style="display: ${['select', 'radio', 'checkbox'].includes(el.inputType) ? 'block' : 'none'}">
                                        <label class="label-base">Options List (Option 1, Option 2...)</label>
                                        <input type="text" class="input-base" value="${el.options || ''}" oninput="builder.updateElement(${index}, 'options', this.value)">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="switch-wrap">
                                            <input type="checkbox" ${el.required ? 'checked' : ''} onchange="builder.updateElement(${index}, 'required', this.checked)" id="req_${index}" style="display:none">
                                            <div class="switch-ui" onclick="const ck = document.getElementById('req_${index}'); ck.checked = !ck.checked; ck.dispatchEvent(new Event('change'))"></div>
                                            <span class="small fw-semibold">Mandatory</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                return div;
            },

            autoSlugAndSave(index, val) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                sec.content[index].label = val;
                const newSlug = this.slugify(val);
                sec.content[index].name = newSlug;
                const nameInput = document.getElementById('name_input_' + index);
                if (nameInput) nameInput.value = newSlug;
            },

            updateElement(index, key, val) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                sec.content[index][key] = val;
                if (key === 'inputType') {
                    const wrapper = document.getElementById('options_wrapper_' + index);
                    if (wrapper) wrapper.style.display = ['select', 'radio', 'checkbox'].includes(val) ? 'block' : 'none';
                }
            },

            validateUniqueNames() {
                let names = [];
                let valid = true;
                this.sections.forEach(sec => {
                    sec.content.forEach(el => {
                        if (el.type === 'input') {
                            if (names.includes(el.name) || el.name.trim() === '') {
                                valid = false;
                                alert('Duplicate or invalid Key: ' + el.name);
                            }
                            names.push(el.name);
                        }
                    });
                });
                return valid;
            },

            saveForm() {
                if (!this.validateUniqueNames()) return;
                this.save(true); // Call unified AJAX save with manual flag
            }
        };

        window.saveActiveTab = function () {
            const coreForm = document.getElementById('coreExamForm');
            const isCore = coreForm && coreForm.style.display === 'block';
            
            if (isCore) {
                // Manually trigger builder.save for core logic to keep things unified
                builder.save(true);
            } else {
                builder.saveForm();
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            builder.init();

            const sourceType = document.getElementById('exam_source_type');
            const owningOrgWrapper = document.getElementById('owningOrgWrapper');
            const owningOrgSelect = owningOrgWrapper ? owningOrgWrapper.querySelector('select') : null;

            function toggleOwningOrg() {
                if (!sourceType || !owningOrgWrapper) return;
                if (sourceType.value === 'External') {
                    $(owningOrgWrapper).hide();
                    if (owningOrgSelect) {
                        $(owningOrgSelect).val('').trigger('change');
                        owningOrgSelect.disabled = true;
                    }
                } else {
                    $(owningOrgWrapper).show();
                    if (owningOrgSelect) owningOrgSelect.disabled = false;
                }
            }

            if (sourceType) {
                toggleOwningOrg();
                sourceType.addEventListener('change', toggleOwningOrg);
            }

            // Initialize Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2-multi').select2({
                    placeholder: "Select Options",
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap4'
                });
                $('.select2-single').select2({
                    width: '100%',
                    theme: 'bootstrap4'
                });
            }
        });

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `<div class="mb-2"><img src="${e.target.result}" height="40" class="rounded border"></div>`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush