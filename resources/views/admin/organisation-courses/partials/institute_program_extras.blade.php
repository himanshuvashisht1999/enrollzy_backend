{{-- programme create / edit, coaching institutes (type 3): product type, audience, batches, test series, fees, results and toppers
     (App\Support\Institutes\ProgramFields). $organisationCourse is not set on create. --}}
@php
    $PF = \App\Support\Institutes\ProgramFields::class;
    $oc = $organisationCourse ?? null;
    $v = fn ($f) => old($f, $oc?->$f);
    $on = fn ($f) => (bool) old($f, $oc?->$f);
    $picked = fn ($f) => (array) (old($f, $oc?->$f) ?? []);
    $batches = array_values((array) old('batches', $oc ? $oc->batches->map(fn ($b) => ['name' => $b->name, 'start_date' => $b->start_date?->format('Y-m-d'),
        'timing' => $b->timing, 'timing_details' => $b->timing_details, 'medium' => $b->medium, 'total_seats' => $b->total_seats, 'seats_left' => $b->seats_left, 'status' => $b->status])->all() : []));
    $results = array_values((array) old('results', $oc ? $oc->results->map->only(['year', 'selections', 'top_rank', 'notes'])->all() : []));
    $topperRows = $oc ? $oc->toppers->keyBy('id') : collect();
    $toppers = array_values((array) old('toppers', $topperRows->map(fn ($t) => $t->only(['id', 'name', 'rank', 'year', 'exam', 'consent']))->values()->all()));
    if (!$batches) $batches = [[]];
    if (!$results) $results = [[]];
    if (!$toppers) $toppers = [[]];
    $opt = fn ($list, $cur) => collect($list)->map(fn ($o) => '<option value="' . e($o) . '"' . ((string) $cur === $o ? ' selected' : '') . '>' . e($o) . '</option>')->implode('');
    $switch = function ($name, $label) use ($on) {
        return '<div class="form-check form-switch mt-4"><input type="hidden" name="' . $name . '" value="0">'
            . '<input class="form-check-input" type="checkbox" name="' . $name . '" value="1" id="px_' . $name . '"' . ($on($name) ? ' checked' : '') . '>'
            . '<label class="form-check-label" for="px_' . $name . '">' . e($label) . '</label></div>';
    };
@endphp
<input type="hidden" name="program_extras_form" value="1">

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-bold mb-0 text-primary">Programme Type & Audience</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label fw-bold" for="px_product_type">Product Type</label>
                <select name="product_type" id="px_product_type" class="form-select">
                    <option value="">Select</option>{!! $opt($PF::PRODUCT_TYPES, $v('product_type')) !!}
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold" for="px_target_classes">Target Students</label>
                <input type="hidden" name="target_classes" value="">
                <select name="target_classes[]" id="px_target_classes" class="form-select select2" multiple style="width:100%" data-placeholder="Who is this programme for?">
                    @foreach($PF::TARGET_CLASSES as $o)
                        <option value="{{ $o }}" @selected(in_array($o, $picked('target_classes'), true))>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" for="px_access_validity_months">Access Validity (months)</label>
                <input type="number" name="access_validity_months" id="px_access_validity_months" class="form-control" value="{{ $v('access_validity_months') }}" min="0" max="240" placeholder="e.g. 12">
                <div class="form-text">For online, recorded and test-series access.</div>
            </div>
            <div class="col-md-4">{!! $switch('demo_class_available', 'Free demo class available') !!}</div>
            <div class="col-md-4">
                <label class="form-label fw-bold" for="px_demo_video_url">Demo Video Link</label>
                <input type="url" name="demo_video_url" id="px_demo_video_url" class="form-control" value="{{ $v('demo_video_url') }}" maxlength="255" placeholder="https://www.youtube.com/watch?v=...">
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-primary">Batches</h5>
            <div class="small text-muted">One row per batch. Rows without a name or start date are not saved. A batch with 0 seats left is saved as Full.</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" data-px-add="#px-batches"><i class="fas fa-plus me-1"></i> Add batch</button>
    </div>
    <div class="card-body p-4 pt-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Batch name</th><th style="width:150px">Starts on</th><th style="width:120px">Timing</th><th style="width:140px">Time</th><th style="width:115px">Medium</th><th style="width:95px">Total seats</th><th style="width:95px">Seats left</th><th style="width:130px">Status</th><th style="width:40px"></th></tr></thead>
                <tbody id="px-batches" data-next="{{ count($batches) }}" data-template="px-batch-row">
                    @foreach($batches as $i => $b)
                        @include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'batch', 'i' => $i, 'd' => $b])
                    @endforeach
                </tbody>
            </table>
        </div>
        <template id="px-batch-row">@include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'batch', 'i' => '__i__', 'd' => []])</template>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-bold mb-0 text-primary">Test Series</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_total_tests">Total Tests</label>
                <input type="number" name="total_tests" id="px_total_tests" class="form-control" value="{{ $v('total_tests') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_full_syllabus_tests_count">Full-length Tests</label>
                <input type="number" name="full_syllabus_tests_count" id="px_full_syllabus_tests_count" class="form-control" value="{{ $v('full_syllabus_tests_count') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_sectional_tests_count">Sectional Tests</label>
                <input type="number" name="sectional_tests_count" id="px_sectional_tests_count" class="form-control" value="{{ $v('sectional_tests_count') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_tests_per_month">Tests per Month</label>
                <input type="number" name="tests_per_month" id="px_tests_per_month" class="form-control" value="{{ $v('tests_per_month') }}" min="0">
            </div>
            <div class="col-md-4">{!! $switch('test_series_available', 'Test series included') !!}</div>
            <div class="col-md-4">{!! $switch('all_india_rank_available', 'All-India rank in tests') !!}</div>
            <div class="col-md-4">{!! $switch('performance_analysis_available', 'Performance analysis after each test') !!}</div>
        </div>
        <div class="form-text mt-2">Validity of the test series: Access Validity, above.</div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-bold mb-0 text-primary">Fee Details</h5>
        <div class="small text-muted">Total Fees is in the first card. Amounts in ₹.</div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_fee_min">Fee Range: from ₹</label>
                <input type="number" name="fee_min" id="px_fee_min" class="form-control" value="{{ $v('fee_min') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_fee_max">Fee Range: to ₹</label>
                <input type="number" name="fee_max" id="px_fee_max" class="form-control" value="{{ $v('fee_max') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_early_bird_discount_percent">Early-bird Discount %</label>
                <input type="number" name="early_bird_discount_percent" id="px_early_bird_discount_percent" class="form-control" value="{{ $v('early_bird_discount_percent') }}" min="0" max="100" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_scholarship_discount_max_percent">Scholarship Discount up to %</label>
                <input type="number" name="scholarship_discount_max_percent" id="px_scholarship_discount_max_percent" class="form-control" value="{{ $v('scholarship_discount_max_percent') }}" min="0" max="100" step="0.01">
            </div>
            <div class="col-md-3">{!! $switch('installment_available', 'Instalments / EMI available') !!}</div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_emi_amount">EMI Amount ₹ / month</label>
                <input type="number" name="emi_amount" id="px_emi_amount" class="form-control" value="{{ $v('emi_amount') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_emi_tenure_months">EMI Tenure (months)</label>
                <input type="number" name="emi_tenure_months" id="px_emi_tenure_months" class="form-control" value="{{ $v('emi_tenure_months') }}" min="1" max="120">
            </div>
            <div class="col-md-3">{!! $switch('gst_included', 'Fee includes GST') !!}</div>
            <div class="col-md-6">
                <label class="form-label fw-bold" for="px_fee_includes">The Fee Covers</label>
                <input type="hidden" name="fee_includes" value="">
                <select name="fee_includes[]" id="px_fee_includes" class="form-select select2" multiple style="width:100%" data-placeholder="Pick all that are included">
                    @foreach($PF::FEE_INCLUDES as $o)
                        <option value="{{ $o }}" @selected(in_array($o, $picked('fee_includes'), true))>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">{!! $switch('scholarship_available', 'Scholarship available') !!}</div>
            <div class="col-md-3">{!! $switch('refund_policy_available', 'Refund policy available') !!}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-bold mb-0 text-primary">Results</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_total_selections_all_time">Total Selections (All Time)</label>
                <input type="number" name="total_selections_all_time" id="px_total_selections_all_time" class="form-control" value="{{ $v('total_selections_all_time') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_selections_last_year">Selections Last Year</label>
                <input type="number" name="selections_last_year" id="px_selections_last_year" class="form-control" value="{{ $v('selections_last_year') }}" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_highest_rank_achieved">Highest Rank (AIR)</label>
                <input type="number" name="highest_rank_achieved" id="px_highest_rank_achieved" class="form-control" value="{{ $v('highest_rank_achieved') }}" min="0" placeholder="e.g. 15 for AIR 15">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="px_average_selection_rate">Selection Rate %</label>
                <input type="number" name="average_selection_rate" id="px_average_selection_rate" class="form-control" value="{{ $v('average_selection_rate') }}" min="0" max="100" step="0.01">
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                <h6 class="fw-bold mb-0">Year-wise Results</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-px-add="#px-results"><i class="fas fa-plus me-1"></i> Add year</button>
            </div>
            <div class="col-12 mt-2">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th style="width:120px">Year</th><th style="width:150px">Selections</th><th style="width:150px">Top rank (AIR)</th><th>Notes</th><th style="width:40px"></th></tr></thead>
                        <tbody id="px-results" data-next="{{ count($results) }}" data-template="px-result-row">
                            @foreach($results as $i => $r)
                                @include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'result', 'i' => $i, 'd' => $r])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <template id="px-result-row">@include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'result', 'i' => '__i__', 'd' => []])</template>
                <div class="form-text">Rows without a year are not saved. Each year once.</div>
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                <h6 class="fw-bold mb-0">Toppers</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-px-add="#px-toppers"><i class="fas fa-plus me-1"></i> Add topper</button>
            </div>
            <div class="col-12 mt-2">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th style="width:230px">Photo</th><th>Name</th><th style="width:110px">Rank (AIR)</th><th style="width:150px">Exam</th><th style="width:100px">Year</th><th style="width:110px">Consent</th><th style="width:40px"></th></tr></thead>
                        <tbody id="px-toppers" data-next="{{ count($toppers) }}" data-template="px-topper-row">
                            @foreach($toppers as $i => $t)
                                @include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'topper', 'i' => $i, 'd' => $t, 'photo' => $topperRows->get($t['id'] ?? null)?->photo_path])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <template id="px-topper-row">@include('admin.organisation-courses.partials.institute_program_rows', ['row' => 'topper', 'i' => '__i__', 'd' => [], 'photo' => null])</template>
                <div class="form-text">Photo: JPG, PNG or WebP, up to 2 MB. The website shows a topper only when Consent is ticked (the student or parent agreed). Rows without a name are not saved.</div>
            </div>
        </div>
    </div>
</div>

@once
@push('js')
<script>
    // Batches, year-wise results, toppers: add a row (from its template, with a fresh index) / remove a row
    document.addEventListener('click', function (e) {
        const add = e.target.closest('[data-px-add]');
        if (add) {
            const body = document.querySelector(add.dataset.pxAdd), tpl = document.getElementById(body.dataset.template);
            const i = parseInt(body.dataset.next, 10) || 0;
            body.dataset.next = i + 1;
            body.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, i));
        }
        const rm = e.target.closest('.px-remove');
        if (rm) rm.closest('tr').remove();
    });
</script>
@endpush
@endonce
