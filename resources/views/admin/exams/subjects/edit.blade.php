@extends('admin.layouts.master')

@section('title', 'Edit Exam Subject')

@section('content')
    <div class="container-fluid p-0">
        <form action="{{ route('admin.exam-subjects.update', $examSubject->id) }}" method="POST" id="subject-form">
            @csrf
            @method('PUT')

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 fw-bold">Edit Exam Subject: <span
                            class="text-primary">{{ $examSubject->subject_name }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="{{ route('admin.exams.index') }}">Exams List</a></li>
                            <li class="breadcrumb-item small"><a
                                    href="{{ route('admin.exam-subjects.index', ['exam_id' => $examSubject->exam_id, 'exam_stage_id' => $examSubject->exam_stage_id]) }}">Subjects
                                    List</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.exam-subjects.index', ['exam_id' => $examSubject->exam_id, 'exam_stage_id' => $examSubject->exam_stage_id]) }}"
                        class="btn btn-light border rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save me-2"></i>Update Subject
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-0 border-bottom">
                    <ul class="nav nav-tabs card-header-tabs border-0" id="subjectTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-3 px-4 border-0 border-bottom-2" id="core-tab"
                                data-bs-toggle="tab" data-bs-target="#core" type="button">
                                <i class="fas fa-id-card me-2"></i>1. Core Identity
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 border-bottom-2" id="selection-tab"
                                data-bs-toggle="tab" data-bs-target="#selection" type="button">
                                <i class="fas fa-tasks me-2"></i>2. Rules & Selection
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 border-bottom-2" id="syllabus-tab"
                                data-bs-toggle="tab" data-bs-target="#syllabus" type="button">
                                <i class="fas fa-book-open me-2"></i>3. Syllabus
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 border-bottom-2" id="marking-tab"
                                data-bs-toggle="tab" data-bs-target="#marking" type="button">
                                <i class="fas fa-percentage me-2"></i>4. Paper & Marking
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 border-bottom-2" id="mapping-tab"
                                data-bs-toggle="tab" data-bs-target="#mapping" type="button">
                                <i class="fas fa-project-diagram me-2"></i>5. Mapping & Result
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4 border-0 border-bottom-2" id="seo-tab" data-bs-toggle="tab"
                                data-bs-target="#seo" type="button">
                                <i class="fas fa-search me-2"></i>6. SEO & Meta
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="subjectTabsContent">

                        <!-- 1. CORE IDENTITY -->
                        <div class="tab-pane fade show active" id="core" role="tabpanel">
                            <h5 class="fw-bold mb-4 text-primary">Core Identity</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Exam <span class="text-danger">*</span></label>
                                    <select name="exam_id" class="form-select select2" required>
                                        <option value="">Select Exam</option>
                                        @foreach($exams as $exam)
                                            <option value="{{ $exam->id }}" {{ old('exam_id', $examSubject->exam_id) == $exam->id ? 'selected' : '' }}>
                                                {{ $exam->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Exam Stage <span
                                            class="text-danger">*</span></label>
                                    <select name="exam_stage_id" class="form-select select2" required>
                                        <option value="">Select Stage</option>
                                        @foreach($stages as $stage)
                                            <option value="{{ $stage->id }}" {{ old('exam_stage_id', $examSubject->exam_stage_id) == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Subject Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="subject_name" class="form-control"
                                        value="{{ old('subject_name', $examSubject->subject_name) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Subject Code</label>
                                    <input type="text" name="subject_code" class="form-control"
                                        value="{{ old('subject_code', $examSubject->subject_code) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Subject Type</label>
                                    <select name="subject_type" class="form-select select2">
                                        @foreach(['Mandatory', 'Optional', 'Language', 'Qualifying', 'Elective', 'Background'] as $type)
                                            <option value="{{ $type }}" {{ old('subject_type', $examSubject->subject_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Subject Group</label>
                                    <input type="text" name="subject_group" class="form-control"
                                        value="{{ old('subject_group', $examSubject->subject_group) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control"
                                        value="{{ old('display_order', $examSubject->display_order) }}">
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch p-0 pt-2 ms-4">
                                        <input class="form-check-input" type="checkbox" name="status" value="Active"
                                            id="status" {{ $examSubject->status == 'Active' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small ms-2" for="status">Is Active?</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. APPLICABILITY & SELECTION RULES -->
                        <div class="tab-pane fade" id="selection" role="tabpanel">
                            <h5 class="fw-bold mb-4 text-primary">Rules & Selection</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Max Subjects Allowed to Select</label>
                                    <input type="number" name="max_subjects_allowed" class="form-control"
                                        value="{{ old('max_subjects_allowed', $examSubject->max_subjects_allowed) }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="subject_choice_required"
                                            id="choice_required" value="1" {{ $examSubject->subject_choice_required ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="choice_required">Is this subject
                                            a choice for candidates?</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Applicable Categories</label>
                                    @php $selectedCats = is_array($examSubject->applicable_categories) ? $examSubject->applicable_categories : []; @endphp
                                    <select name="applicable_categories[]" class="form-select select2" multiple
                                        data-placeholder="Select Categories">
                                        @foreach(['General', 'OBC', 'SC', 'ST', 'EWS', 'PwD', 'Female', 'Ex-servicemen', 'Others'] as $cat)
                                            <option value="{{ $cat }}" {{ in_array($cat, old('applicable_categories', $selectedCats)) ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Mediums Available</label>
                                    @php $selectedLangs = is_array($examSubject->subject_mediums_available) ? $examSubject->subject_mediums_available : []; @endphp
                                    <select name="subject_mediums_available[]" class="form-select select2" multiple
                                        data-placeholder="Select Languages">
                                        @foreach(['English', 'Hindi', 'Bengali', 'Telugu', 'Marathi', 'Tamil', 'Urdu', 'Gujarati', 'Kannada', 'Odia', 'Malayalam', 'Punjabi', 'Assamese', 'Maithili', 'Santhali', 'Kashmiri', 'Nepali', 'Sindhi', 'Konkani', 'Manipuri', 'Dogri', 'Bodo', 'Sanskrit'] as $lang)
                                            <option value="{{ $lang }}" {{ in_array($lang, old('subject_mediums_available', $selectedLangs)) ? 'selected' : '' }}>{{ $lang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Subject Combination Rules</label>
                                    <textarea name="subject_combination_rules" class="form-control"
                                        rows="3">{{ $examSubject->subject_combination_rules }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 3. SYLLABUS -->
                        <div class="tab-pane fade" id="syllabus" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0 text-primary">Syllabus Structure</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                    id="add-unit">
                                    <i class="fas fa-plus me-1"></i>Add Unit/Chapter
                                </button>
                            </div>

                            <div id="units-container" class="mb-4">
                                @php $syllabus = $examSubject->syllabus_structure ?? []; @endphp
                                @foreach($syllabus as $index => $unit)
                                    <div class="unit-item card border mb-3 bg-light rounded-3 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="fw-bold mb-0 text-dark"><i
                                                        class="fas fa-bookmark me-2 text-primary"></i>Unit #<span
                                                        class="unit-index">{{ $index + 1 }}</span></h6>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger border-0 remove-unit rounded-circle"><i
                                                        class="fas fa-times"></i></button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label class="small fw-bold">Unit No.</label>
                                                    <input type="text" name="syllabus[{{ $index }}][unit_number]"
                                                        class="form-control form-control-sm border-0 shadow-sm"
                                                        value="{{ $unit['unit_number'] ?? '' }}" required>
                                                </div>
                                                <div class="col-md-10">
                                                    <label class="small fw-bold">Unit Title</label>
                                                    <input type="text" name="syllabus[{{ $index }}][unit_title]"
                                                        class="form-control form-control-sm border-0 shadow-sm"
                                                        value="{{ $unit['unit_title'] ?? '' }}" required>
                                                </div>
                                                <div class="col-md-12 mt-2">
                                                    <label class="small fw-bold">Topics Covered (Multi-tags)</label>
                                                    <select name="syllabus[{{ $index }}][topics][]"
                                                        class="form-select select2-tags" multiple
                                                        data-placeholder="Type topic and press enter">
                                                        @if(isset($unit['topics']) && is_array($unit['topics']))
                                                            @foreach($unit['topics'] as $topic)
                                                                <option value="{{ $topic }}" selected>{{ $topic }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-4">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Detailed Syllabus Description</label>
                                    <textarea name="syllabus_description" class="form-control editor"
                                        rows="4">{{ $examSubject->syllabus_description }}</textarea>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Official Syllabus PDF Link</label>
                                    <input type="url" name="official_syllabus_pdf_url" class="form-control"
                                        value="{{ $examSubject->official_syllabus_pdf_url }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Effective Year</label>
                                    <input type="text" name="syllabus_effective_year" class="form-control"
                                        value="{{ $examSubject->syllabus_effective_year }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Reference Books</label>
                                    <select name="reference_books[]" class="form-select select2" multiple data-tags="true"
                                        data-placeholder="Add book names">
                                        @if(is_array($examSubject->reference_books))
                                            @foreach($examSubject->reference_books as $book)
                                                <option value="{{ $book }}" selected>{{ $book }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 4. PAPER & MARKING -->
                        <div class="tab-pane fade" id="marking" role="tabpanel">
                            <h5 class="fw-bold mb-4 text-primary">Paper & Marking Structure</h5>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Number of Papers</label>
                                    <input type="number" name="number_of_papers" class="form-control"
                                        value="{{ $examSubject->number_of_papers }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Paper Names</label>
                                    <select name="paper_names[]" class="form-select select2" multiple data-tags="true"
                                        data-placeholder="e.g. Paper I">
                                        @if(is_array($examSubject->paper_names))
                                            @foreach($examSubject->paper_names as $paper)
                                                <option value="{{ $paper }}" selected>{{ $paper }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Total Maximum Marks</label>
                                    <input type="number" name="total_marks" class="form-control"
                                        value="{{ $examSubject->total_marks }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Qualifying Marks</label>
                                    <input type="number" name="qualifying_marks" class="form-control"
                                        value="{{ $examSubject->qualifying_marks }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="negative_marking"
                                            id="neg_marking" value="1" {{ $examSubject->negative_marking ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="neg_marking">Negative Marking
                                            Applied?</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="normalization_applied"
                                            id="norm" value="1" {{ $examSubject->normalization_applied ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="norm">Normalization
                                            Applied?</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. MAPPING & RESULT -->
                        <div class="tab-pane fade" id="mapping" role="tabpanel">
                            <h5 class="fw-bold mb-4 text-primary">Stage Mapping & Result Rules</h5>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Applicable across Exam Stages</label>
                                    @php $selectedStagesStored = is_array($examSubject->applicable_exam_stages) ? $examSubject->applicable_exam_stages : []; @endphp
                                    <select name="applicable_exam_stages[]" class="form-select select2" multiple
                                        data-placeholder="Select Stages">
                                        @foreach(['Prelims', 'Mains', 'Interview', 'Skill Test', 'Medical', 'Physical', 'Document Verification'] as $stg)
                                            <option value="{{ $stg }}" {{ in_array($stg, old('applicable_exam_stages', $selectedStagesStored)) ? 'selected' : '' }}>{{ $stg }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="subject_contributes_to_merit"
                                            id="merit" value="1" {{ $examSubject->subject_contributes_to_merit ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="merit">Contributes to Final Merit
                                            List?</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="background_subject_required"
                                            id="bg_sub" value="1" {{ $examSubject->background_subject_required ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="bg_sub">Background Subject
                                            Required?</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Subject Weightage in Stage (%)</label>
                                    <input type="number" step="0.01" name="subject_weightage_percentage"
                                        class="form-control" value="{{ $examSubject->subject_weightage_percentage }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Result Selection Type</label>
                                    <select name="subject_result_type" class="form-select select2">
                                        @foreach(['Marks', 'Pass/Fail', 'Grades', 'Percentile'] as $resType)
                                            <option value="{{ $resType }}" {{ $examSubject->subject_result_type == $resType ? 'selected' : '' }}>{{ $resType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 6. SEO & META -->
                        <div class="tab-pane fade" id="seo" role="tabpanel">
                            <h5 class="fw-bold mb-4 text-primary">SEO & Metadata</h5>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control"
                                        value="{{ $examSubject->meta_title }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Meta Description</label>
                                    <textarea name="meta_description" class="form-control"
                                        rows="3">{{ $examSubject->meta_description }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Focus Keywords</label>
                                    <select name="focus_keywords[]" class="form-select select2" multiple data-tags="true"
                                        data-placeholder="Add keywords">
                                        @if(is_array($examSubject->focus_keywords))
                                            @foreach($examSubject->focus_keywords as $kw)
                                                <option value="{{ $kw }}" selected>{{ $kw }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Template for Syllabus Unit -->
    <template id="unit-template">
        <div class="unit-item card border mb-3 bg-light rounded-3 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-bookmark me-2 text-primary"></i>Unit #<span
                            class="unit-index">INDEX</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-unit rounded-circle"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="small fw-bold">Unit No.</label>
                        <input type="text" name="syllabus[INDEX][unit_number]"
                            class="form-control form-control-sm border-0 shadow-sm" placeholder="e.g. I" required>
                    </div>
                    <div class="col-md-10">
                        <label class="small fw-bold">Unit Title</label>
                        <input type="text" name="syllabus[INDEX][unit_title]"
                            class="form-control form-control-sm border-0 shadow-sm" placeholder="e.g. Title" required>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="small fw-bold">Topics Covered (Multi-tags)</label>
                        <select name="syllabus[INDEX][topics][]" class="form-select select2-tags" multiple
                            data-placeholder="Type topic and press enter">
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </template>

@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs .nav-link:hover {
            color: #0d6efd;
            background: transparent;
            border-bottom-color: #dee2e6;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border-bottom: 3px solid #0d6efd !important;
        }

        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 45px;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px 8px;
            margin-top: 7px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            function initSelect2() {
                $('.select2').each(function () {
                    $(this).select2({ width: '100%', dropdownParent: $(this).parent() });
                });
                $('.select2-tags').each(function () {
                    $(this).select2({ tags: true, width: '100%', tokenSeparators: [','], dropdownParent: $(this).parent() });
                });
            }

            initSelect2();

            // CKEditor
            document.querySelectorAll('.editor').forEach(elem => {
                ClassicEditor.create(elem).catch(error => console.error(error));
            });

            // Syllabus Repeater
            let unitIndex = {{ count(is_array($examSubject->syllabus_structure) ? $examSubject->syllabus_structure : []) }};
            const container = $('#units-container');
            const template = document.getElementById('unit-template').innerHTML;

            function addUnit() {
                let html = template.replace(/INDEX/g, unitIndex);
                container.append(html);
                container.find('.unit-index').last().text(unitIndex + 1);
                container.find('.select2-tags').last().select2({ tags: true, width: '100%' });
                unitIndex++;
            }

            $('#add-unit').click(addUnit);

            $(document).on('click', '.remove-unit', function () {
                $(this).closest('.unit-item').fadeOut(300, function () {
                    $(this).remove();
                    $('#units-container .unit-item').each(function (idx) {
                        $(this).find('.unit-index').text(idx + 1);
                    });
                    unitIndex = $('#units-container .unit-item').length;
                });
            });
        });
    </script>
@endpush