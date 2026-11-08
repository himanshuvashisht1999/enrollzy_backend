{{-- University / college course form in tabs: Course · Admission · Fees & charges · Course content · Documents.
     part=open: the tab bar and the start of the Course tab (the form's own course / campus card follows, then uc_fields);
     part=close: the end of the Course tab and the other tabs. $oc: the organisation course on edit, null on create.
     A tab with a validation error is marked, and the browser's "fill this in" opens the tab that holds the field. --}}
@php
    $tabs = [
        'course' => ['Course', 'fa-book', ['course_id', 'campus_id', 'department_id', 'mode', 'student_strength', 'status', 'specialization_ids', 'course_languages', 'rating', 'roi', 'sort_order']],
        'admission' => ['Admission', 'fa-door-open', ['entrance_exam_category', 'entrance_exam_ids', 'entrance_exam_ids.*', 'provisional_admission', 'admission_process', 'eligibility']],
        'fees' => ['Fees & other charges', 'fa-rupee-sign', ['fees', 'total_fees', 'fees_structure', 'annual_fee_range', 'admission_fee', 'fee_payment_frequency', 'average_course_fee_range']],
        'content' => ['Course content', 'fa-align-left', ['curriculum', 'career_prospects', 'placement_details', 'industrial_collaboration', 'internship_ranking']],
        'docs' => ['Documents', 'fa-file-pdf', ['documents', 'documents.*']],
    ];
    $keys = array_keys($tabs);
    $v = fn ($k) => old($k, isset($oc) && $oc ? $oc->$k : null);
    $pager = function (string $k) use ($keys, $tabs) {
        $i = array_search($k, $keys, true);
        $prev = $keys[$i - 1] ?? null; $next = $keys[$i + 1] ?? null;
        return '<div class="uc-pager">' . ($prev ? '<button type="button" class="btn btn-light" data-uc-go="' . $prev . '"><i class="fas fa-arrow-left me-1"></i> ' . e($tabs[$prev][0]) . '</button>' : '<span></span>')
            . ($next ? '<button type="button" class="btn btn-outline-primary" data-uc-go="' . $next . '">Next: ' . e($tabs[$next][0]) . ' <i class="fas fa-arrow-right ms-1"></i></button>' : '') . '</div>';
    };
@endphp

@if($part === 'open')
    @once
        <style>
            .uc-tabs { gap: 6px; border: 0; background: #fff; border-radius: 14px; padding: 6px; box-shadow: 0 1px 2px rgba(15,33,71,.05), 0 6px 20px rgba(31,95,216,.06); flex-wrap: wrap; }
            .uc-tabs .nav-link { border: 0; border-radius: 10px; color: #45546f; font-weight: 600; padding: 10px 16px; display: inline-flex; align-items: center; gap: 8px; }
            .uc-tabs .nav-link .n { width: 22px; height: 22px; border-radius: 50%; background: #eef2f9; color: #45546f; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
            .uc-tabs .nav-link.active { background: #1f5fd8; color: #fff; }
            .uc-tabs .nav-link.active .n { background: rgba(255,255,255,.25); color: #fff; }
            .uc-tabs .nav-link.has-error { box-shadow: inset 0 0 0 2px #dc3545; }
            .uc-tabs .nav-link.has-error::after { content: '!'; color: #dc3545; font-weight: 800; }
            .uc-tabs .nav-link.active.has-error::after { color: #fff; }
            .uc-pager { display: flex; justify-content: space-between; gap: 10px; margin-top: 16px; }
            .uc-sub { font-size: 13px; color: #6c757d; margin: 2px 0 0; }
        </style>
    @endonce
    <div class="col-12" id="uc-tabs">
        <ul class="nav uc-tabs mb-3" role="tablist">
            @foreach($tabs as $k => [$label, $icon, $fields])
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($loop->first) active @endif @if($errors->hasAny($fields)) has-error @endif" id="uc-{{ $k }}-tab" data-bs-toggle="tab" data-bs-target="#uc-{{ $k }}" type="button" role="tab" aria-controls="uc-{{ $k }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"><span class="n">{{ $loop->iteration }}</span>{{ $label }}</button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="uc-course" role="tabpanel" aria-labelledby="uc-course-tab">
@else
                {!! $pager('course') !!}
            </div>

            {{-- Admission --}}
            <div class="tab-pane fade" id="uc-admission" role="tabpanel" aria-labelledby="uc-admission-tab">
                @php
                    $selectedExams = old('entrance_exam_ids', $oc ? ($oc->entrance_exam_ids ?? []) : []);
                    if (!is_array($selectedExams)) $selectedExams = json_decode((string) $selectedExams, true) ?: [];
                @endphp
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-door-open me-2"></i>Admission</h5>
                        <p class="uc-sub">How students get in at this college: the entrance exams, the process and who is eligible.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Exam Category (Filter)</label>
                                <select name="entrance_exam_category" class="form-select select2 exam-category-filter">
                                    <option value="">-- All Categories --</option>
                                    @foreach($examCategories as $cat)
                                        <option value="{{ $cat }}" @selected($v('entrance_exam_category') == $cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Admission Route (Exams)</label>
                                <select name="entrance_exam_ids[]" class="form-select select2 entrance-exam-ids-select" multiple>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" data-category="{{ is_array($exam->exam_category) ? implode(',', $exam->exam_category) : ($exam->exam_category ?? '') }}" @selected(in_array($exam->id, $selectedExams))>{{ $exam->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Leave empty for direct / merit admission.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Provisional Admission</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="provisional_admission" value="0">
                                    <input class="form-check-input" type="checkbox" name="provisional_admission" id="provisional_admission" value="1" @checked($v('provisional_admission'))>
                                    <label class="form-check-label" for="provisional_admission">Available</label>
                                </div>
                                <div class="form-text">Before the qualifying result is out.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Admission Process</label>
                                <textarea name="admission_process" class="editor">{{ $v('admission_process') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Eligibility Criteria</label>
                                <textarea name="eligibility" class="editor">{{ $v('eligibility') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                {!! $pager('admission') !!}
            </div>

            {{-- Fees & other charges --}}
            <div class="tab-pane fade" id="uc-fees" role="tabpanel" aria-labelledby="uc-fees-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-rupee-sign me-2"></i>Fees &amp; other charges</h5>
                        <p class="uc-sub">Tuition and every other charge at this college. Leave blank what you do not know.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fees per Year</label>
                                <input type="text" name="fees" class="form-control" value="{{ $v('fees') }}" placeholder="e.g. ₹ 1,50,000 / Year">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total Course Fee (₹)</label>
                                <input type="number" step="0.01" min="0" name="total_fees" class="form-control" value="{{ $v('total_fees') }}" placeholder="e.g. 600000">
                            </div>
                            @include('admin.organisation-courses.partials.extra-fields', ['only' => ['annual_fee_range', 'admission_fee', 'fee_payment_frequency', 'average_course_fee_range', 'installment_available', 'scholarship_available', 'refund_policy_available'], 'bare' => true])
                            <div class="col-12">
                                <label class="form-label fw-bold">Fees Structure Details</label>
                                <textarea name="fees_structure" class="editor">{{ $v('fees_structure') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                {!! $pager('fees') !!}
            </div>

            {{-- Course content --}}
            <div class="tab-pane fade" id="uc-content" role="tabpanel" aria-labelledby="uc-content-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-align-left me-2"></i>Course content</h5>
                        <p class="uc-sub">Optional. Shown on the website only when filled here: the master course's text is never used for a college's course.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Curriculum / Syllabus</label>
                                <textarea name="curriculum" class="editor">{{ $v('curriculum') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Career Prospects</label>
                                <textarea name="career_prospects" class="editor">{{ $v('career_prospects') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Placement Details</label>
                                <textarea name="placement_details" class="editor">{{ $v('placement_details') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Industrial & Academic Collaborators</label>
                                <textarea name="industrial_collaboration" class="editor">{{ $v('industrial_collaboration') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Internship & Course Ranking</label>
                                <textarea name="internship_ranking" class="editor">{{ $v('internship_ranking') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                {!! $pager('content') !!}
            </div>

            {{-- Documents --}}
            <div class="tab-pane fade" id="uc-docs" role="tabpanel" aria-labelledby="uc-docs-tab">
                <div class="row">@include('admin.organisation-courses.partials.documents')</div>
                {!! $pager('docs') !!}
            </div>
        </div>
    </div>
    <script>
        (function () {
            var show = function (id) { var b = document.querySelector('#uc-tabs [data-bs-target="#uc-' + id + '"]'); if (b) bootstrap.Tab.getOrCreateInstance(b).show(); };
            document.addEventListener('click', function (e) {
                var b = e.target.closest('[data-uc-go]'); if (!b) return;
                show(b.getAttribute('data-uc-go'));
                document.getElementById('uc-tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            // a required field on a hidden tab: open that tab so the browser can point at it
            document.addEventListener('invalid', function (e) {
                var pane = e.target.closest && e.target.closest('#uc-tabs .tab-pane');
                if (pane && !pane.classList.contains('active')) show(pane.id.replace('uc-', ''));
            }, true);
            // after a failed save, open the first tab with an error
            var err = document.querySelector('#uc-tabs .nav-link.has-error');
            if (err && !err.classList.contains('active')) bootstrap.Tab.getOrCreateInstance(err).show();
        })();
    </script>
@endif
