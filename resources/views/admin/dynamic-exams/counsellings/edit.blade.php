@extends('admin.layouts.master')

@section('title', 'Counselling Structure Builder')

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

        .select2-container .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid var(--app-border);
            border-radius: 8px;
        }

        .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid var(--app-border);
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
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
            <h1 class="h4 fw-bold mb-0">Build Counselling Structure</h1>
            <p class="text-muted small mb-0">{{ $counselling->counselling_name }} ({{ $dynamicExam->name }})</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dynamic-exams.counsellings.index', $dynamicExam->id) }}" class="btn btn-sm btn-outline-secondary px-3">Discard</a>
            <button type="button" class="btn btn-sm btn-primary-app px-4" id="mainSaveBtn"
                onclick="saveActiveTab()">Save</button>
        </div>
    </div>

    <form action="{{ route('admin.dynamic-exams.counsellings.update', [$dynamicExam->id, $counselling->id]) }}" method="POST" id="mainCounsellingForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="sections" id="sectionsDataPayload">

        <div class="main-builder-wrapper">
            <!-- Sidebar -->
            <div class="builder-nav">
                <div class="nav-label">Core Config</div>
                <div class="nav-btn active" id="coreTabBtn" onclick="builder.showCore()">
                    <i class="fas fa-cog"></i> Main Identity
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
                    <div id="coreCounsellingForm" style="display:block;">
                        <div class="section-header">
                            <h2 class="fw-bold mb-1">Main Identity</h2>
                            <p class="text-muted small">Configure global identifiers for this counselling</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-base">Counselling Name <span class="text-danger">*</span></label>
                                <input type="text" name="counselling_name" class="input-base"
                                    value="{{ $counselling->counselling_name }}" required onkeyup="builder.save()">
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Slug</label>
                                <input type="text" name="slug" class="input-base"
                                    value="{{ $counselling->slug }}" onchange="builder.save()">
                            </div>
                            <div class="col-md-4">
                                <label class="label-base">Counselling Type *</label>
                                <select name="counselling_type" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Centralised', 'State-Level', 'Institute-Level'] as $opt)
                                        <option value="{{ $opt }}" {{ $counselling->counselling_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="label-base">Counselling Mode</label>
                                <select name="counselling_mode" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Online', 'Offline', 'Hybrid'] as $opt)
                                        <option value="{{ $opt }}" {{ $counselling->counselling_mode == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="label-base">Conducting Authority Name *</label>
                                <input type="text" name="conducting_authority_name" class="input-base"
                                    value="{{ $counselling->conducting_authority_name }}" onchange="builder.save()">
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Conducting Authority Type</label>
                                <select name="conducting_authority_type" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Central Government', 'State Government', 'University Body'] as $opt)
                                        <option value="{{ $opt }}" {{ $counselling->conducting_authority_type == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Official Website</label>
                                <input type="url" name="official_counselling_website" class="input-base"
                                    value="{{ $counselling->official_counselling_website }}" onchange="builder.save()">
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Visibility</label>
                                <select name="visibility" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Public', 'Draft', 'Private'] as $v)
                                        <option value="{{ $v }}" {{ $counselling->visibility == $v ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-base">Status</label>
                                <select name="status" class="input-base select2-single" onchange="builder.save()">
                                    @foreach(['Active', 'Upcoming', 'Closed', 'Archived'] as $s)
                                        <option value="{{ $s }}" {{ $counselling->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
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
    </form>
@endsection

@push('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const builder = {
            sections: {!! json_encode($counselling->sections->map(function ($s) {
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
                this.initSorting();
                
                const lastActive = sessionStorage.getItem('activeCounsellingSection_' + '{{ $counselling->id }}');
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

            initSorting() {
                const sectionsList = document.getElementById('sectionsList');
                if (sectionsList) {
                    new Sortable(sectionsList, {
                        animation: 150,
                        handle: '.fa-bars',
                        onEnd: (evt) => {
                            const reordered = [];
                            const items = sectionsList.querySelectorAll('.nav-btn');
                            items.forEach(item => {
                                const id = item.getAttribute('data-id');
                                const sec = this.sections.find(s => s.temp_id === id);
                                if (sec) reordered.push(sec);
                            });
                            this.sections = reordered;
                            this.save();
                        }
                    });
                }
            },

            initFieldsSorting() {
                const elementsContainer = document.getElementById('elementsContainer');
                if (elementsContainer) {
                    new Sortable(elementsContainer, {
                        animation: 150,
                        handle: '.field-drag-handle',
                        onEnd: (evt) => {
                            const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                            if (sec) {
                                const newContent = [];
                                const rows = elementsContainer.querySelectorAll('.field-row');
                                rows.forEach(row => {
                                    const index = row.getAttribute('data-index');
                                    newContent.push(sec.content[index]);
                                });
                                sec.content = newContent;
                                this.renderEditor(); // re-render to update data-index
                                this.save();
                            }
                        }
                    });
                }
            },

            generateId() {
                return 'id_' + Math.random().toString(36).substr(2, 9);
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
                sessionStorage.setItem('activeCounsellingSection_' + '{{ $counselling->id }}', 'core');
                const coreBtn = document.getElementById('coreTabBtn');
                if (coreBtn) {
                    coreBtn.classList.add('active');
                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        if (btn.id !== 'coreTabBtn') btn.classList.remove('active');
                    });
                }

                const coreForm = document.getElementById('coreCounsellingForm');
                if (coreForm) coreForm.style.display = 'block';

                document.getElementById('activeSectionEditor').style.display = 'none';
                if (document.getElementById('placeholder')) {
                    document.getElementById('placeholder').style.display = 'none';
                }
            },

            setActive(id) {
                this.activeSectionId = id;
                sessionStorage.setItem('activeCounsellingSection_' + '{{ $counselling->id }}', id);
                const coreBtn = document.getElementById('coreTabBtn');
                if (coreBtn) coreBtn.classList.remove('active');

                const coreForm = document.getElementById('coreCounsellingForm');
                if (coreForm) coreForm.style.display = 'none';

                this.renderSidebar();
                this.renderEditor();
                this.initFieldsSorting();

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

                const form = document.getElementById('mainCounsellingForm');
                const formData = new FormData(form);
                formData.set('sections', JSON.stringify(this.sections));
                formData.set('_method', 'PUT');

                fetch(`{{ route('admin.dynamic-exams.counsellings.update', [$dynamicExam->id, $counselling->id]) }}`, {
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
                    } else {
                        if (isManual) toastr.error('Error', res.message || 'Save failed');
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
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
                    const node = (el.type === 'subheading') 
                        ? this.createSubheadingNode(sec, el, index) 
                        : this.createInputNode(sec, el, index);
                    node.setAttribute('data-index', index);
                    container.appendChild(node);
                });
            },

            addElement(type) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (!sec) return;

                if (type === 'subheading') {
                    sec.content.push({ type: 'subheading', title: 'Group Label' });
                } else {
                    sec.content.push({ type: 'input', inputType: 'textarea', label: 'Field Label', name: 'field_' + Date.now(), required: true, options: '' });
                }
                this.renderEditor();
            },

            createSubheadingNode(sec, el, index) {
                const div = document.createElement('div');
                div.className = 'field-row subheading-row d-flex align-items-center';
                div.innerHTML = `
                        <i class="fas fa-grip-vertical field-drag-handle"></i>
                        <input type="text" class="group-label-input" value="${el.title}" onkeyup="builder.updateElement(${index}, 'title', this.value)">
                        <i class="fas fa-times remove-action ms-auto" onclick="builder.removeElement(${index})"></i>
                    `;
                return div;
            },

            createInputNode(sec, el, index) {
                const div = document.createElement('div');
                div.className = 'field-row';
                div.innerHTML = `
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-grip-vertical field-drag-handle"></i>
                            <span class="fw-bold text-primary">Input Field</span>
                            <i class="fas fa-trash-alt remove-action ms-auto" onclick="builder.removeElement(${index})"></i>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-base">Field Label</label>
                                <input type="text" class="input-base" value="${el.label}" onkeyup="builder.updateElement(${index}, 'label', this.value)">
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Data Name (Unique)</label>
                                <input type="text" class="input-base" value="${el.name}" onkeyup="builder.updateElement(${index}, 'name', this.value)">
                            </div>
                            <div class="col-md-3">
                                <label class="label-base">Input Type</label>
                                <select class="input-base" onchange="builder.updateElement(${index}, 'inputType', this.value)">
                                    <option value="text" ${el.inputType === 'text' ? 'selected' : ''}>Text</option>
                                    <option value="number" ${el.inputType === 'number' ? 'selected' : ''}>Number</option>
                                    <option value="date" ${el.inputType === 'date' ? 'selected' : ''}>Date</option>
                                    <option value="textarea" ${el.inputType === 'textarea' ? 'selected' : ''}>Rich Text (TinyMCE)</option>
                                    <option value="select" ${el.inputType === 'select' ? 'selected' : ''}>Dropdown</option>
                                    <option value="multi-select" ${el.inputType === 'multi-select' ? 'selected' : ''}>Multi Select</option>
                                    <option value="radio" ${el.inputType === 'radio' ? 'selected' : ''}>Radio Buttons</option>
                                    <option value="checkbox" ${el.inputType === 'checkbox' ? 'selected' : ''}>Checkboxes</option>
                                    <option value="file" ${el.inputType === 'file' ? 'selected' : ''}>File Upload</option>
                                </select>
                            </div>
                            <div class="col-md-9" style="${['select', 'multi-select', 'radio', 'checkbox'].includes(el.inputType) ? '' : 'display:none;'}">
                                <label class="label-base">Options (Comma separated)</label>
                                <input type="text" class="input-base" value="${el.options || ''}" placeholder="Option 1, Option 2, Option 3" onkeyup="builder.updateElement(${index}, 'options', this.value)">
                            </div>
                            <div class="col-md-3 d-flex align-items-end pb-1">
                                <div class="form-check form-switch ps-5">
                                    <input class="form-check-input" type="checkbox" ${el.required ? 'checked' : ''} onchange="builder.updateElement(${index}, 'required', this.checked)">
                                    <label class="form-check-label small fw-semibold">Required</label>
                                </div>
                            </div>
                        </div>
                    `;
                return div;
            },

            updateElement(index, key, val) {
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (sec && sec.content[index]) {
                    sec.content[index][key] = val;
                    if (key === 'inputType') this.renderEditor();
                    if (this.saveTimeout) clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => this.save(), 500);
                }
            },

            removeElement(index) {
                if (!confirm('Remove this field?')) return;
                const sec = this.sections.find(s => s.temp_id === this.activeSectionId);
                if (sec) {
                    sec.content.splice(index, 1);
                    this.renderEditor();
                    this.save();
                }
            }
        };

        function saveActiveTab() {
            builder.save(true);
        }

        builder.init();
    </script>
@endpush