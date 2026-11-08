{{--
    A roadmap (no parent) or an item (under a roadmap or another item). The sections follow what is being made:
    a roadmap has a type, a web address and SEO, and a stream roadmap its stream details; an item has a type, its facts
    (eligibility, dates, fee…) and, for an exam, course, scholarship or career, a link to that module.
--}}
@php
    use App\Models\CareerRoadmapSubModule as M;
    $v = fn ($f, $d = null) => old($f, $item->{$f} ?? $d);
    $careerIds = collect(old('career_ids', $item->career_ids ?? []))->map(fn ($x) => (int) $x)->all();
@endphp

@if($errors->any())
    <div class="alert alert-danger"><b>Please check:</b><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
    <h5 class="mb-3">Where it sits</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-bold" for="stage_id">Class <span class="text-danger">*</span></label>
            <select class="form-select" id="stage_id" name="stage_id" required>
                <option value="">Select class</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" @selected((int) $v('stage_id') === $c->id)>{{ $c->title }}{{ $c->category ? ' — ' . $c->category->name : '' }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold" for="parent_id">Under</label>
            <select class="form-select" id="parent_id" name="parent_id">
                <option value="">Nothing: this is a roadmap of the class</option>
                @foreach($parents as $p)<option value="{{ $p['id'] }}" data-stage="{{ $p['stage_id'] }}" @selected((int) $v('parent_id') === $p['id'])>{{ $p['label'] }}</option>@endforeach
            </select>
            <small class="text-muted">Pick the roadmap or step this item belongs to. Leave empty to make a roadmap.</small>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold" for="sort_order">Order</label>
            <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ $v('sort_order', 0) }}">
            <small class="text-muted">Lower first.</small>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-bold" for="title">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $v('title') }}" required maxlength="255">
        </div>
        <div class="col-md-4" data-for="roadmap">
            <label class="form-label fw-bold" for="roadmap_type">Roadmap type <span class="text-danger">*</span></label>
            <select class="form-select" id="roadmap_type" name="roadmap_type">
                <option value="">Select type</option>
                @foreach(M::ROADMAP_TYPES as $k => $label)<option value="{{ $k }}" @selected($v('roadmap_type') === $k)>{{ $label }}</option>@endforeach
            </select>
            <small class="text-muted">Decides the section on the website (Stream options, Career opportunities, Exams & scholarships…) and the page layout.</small>
        </div>
        <div class="col-md-4" data-for="item">
            <label class="form-label fw-bold" for="item_type">Item type <span class="text-danger">*</span></label>
            <select class="form-select" id="item_type" name="item_type">
                <option value="">Select type</option>
                @foreach(M::ITEM_TYPES as $k => $label)<option value="{{ $k }}" @selected($v('item_type') === $k)>{{ $label }}</option>@endforeach
            </select>
            <small class="text-muted">A step holds other items; an exam, course, scholarship or career links to its module.</small>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
    <h5 class="mb-3">Content</h5>
    <div class="mb-3">
        <label class="form-label fw-bold" for="summary">Summary</label>
        <input type="text" class="form-control" id="summary" name="summary" value="{{ $v('summary') }}" maxlength="300" placeholder="One or two lines a student understands at a glance">
        <small class="text-muted">Shown first. Keep it under 300 characters.</small>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold" for="description"><span data-for="roadmap">Tags on the card</span><span data-for="item">Description</span></label>
        <textarea class="form-control" id="description" name="description" rows="4">{{ $v('description') }}</textarea>
        <small class="text-muted" data-for="roadmap">Short words, one per line (e.g. JEE, NEET, CUET). They show under the roadmap's name.</small>
        <small class="text-muted" data-for="item">Plain text; a new line starts a new line on the website.</small>
    </div>
    <div class="mb-3" data-for="item">
        <label class="form-label fw-bold" for="long_description">More details</label>
        <textarea class="form-control" id="long_description" name="long_description" rows="5">{{ $v('long_description') }}</textarea>
        <small class="text-muted">Shown behind "Read more".</small>
    </div>
    <div class="mb-0" data-for="roadmap">
        <label class="form-label fw-bold" for="alert_message">Intro line</label>
        <textarea class="form-control" id="alert_message" name="alert_message" rows="2" maxlength="500">{{ old('alert_message', $cf['alert_message'] ?? '') }}</textarea>
        <small class="text-muted">One or two sentences under the roadmap's heading.</small>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3" data-for="stream"><div class="card-body p-4">
    <h5 class="mb-1">Stream details</h5>
    <p class="text-muted small">For a stream (PCM, PCB, Commerce…): the website shows these in order, then the careers, exams and courses.</p>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold" for="subjects">Subjects</label>
            <textarea class="form-control" id="subjects" name="subjects" rows="5" placeholder="One per line">{{ $v('subjects') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold" for="who_should">Who should choose it</label>
            <textarea class="form-control" id="who_should" name="who_should" rows="5">{{ $v('who_should') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold" for="closes">What it rules out</label>
            <textarea class="form-control" id="closes" name="closes" rows="3" placeholder="One per line, e.g. MBBS through NEET (needs Biology)">{{ $v('closes') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold" for="career_ids">Careers it opens</label>
            <select class="form-select" id="career_ids" name="career_ids[]" multiple>
                @foreach($careers as $c)<option value="{{ $c->id }}" @selected(in_array($c->id, $careerIds, true))>{{ $c->name }}</option>@endforeach
            </select>
            <small class="text-muted">From <a href="{{ route('admin.careers.index') }}" target="_blank">Careers</a>. Their exams and courses show on the stream's page.</small>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3" data-for="item"><div class="card-body p-4">
    <h5 class="mb-1">Facts</h5>
    <p class="text-muted small">Each shows as its own row on the website. Leave empty what does not apply.</p>
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label fw-bold" for="eligibility">Eligibility</label>
            <textarea class="form-control" id="eligibility" name="eligibility" rows="2" placeholder="e.g. Class 8 students of government schools; family income under ₹3.5 lakh">{{ $v('eligibility') }}</textarea>
        </div>
        <div class="col-md-4"><label class="form-label fw-bold" for="conducted_by">Conducted / offered by</label><input type="text" class="form-control" id="conducted_by" name="conducted_by" value="{{ $v('conducted_by') }}" maxlength="190"></div>
        <div class="col-md-4"><label class="form-label fw-bold" for="when_text">When</label><input type="text" class="form-control" id="when_text" name="when_text" value="{{ $v('when_text') }}" maxlength="190" placeholder="e.g. Registration in July, exam in November"></div>
        <div class="col-md-4"><label class="form-label fw-bold" for="duration">Duration</label><input type="text" class="form-control" id="duration" name="duration" value="{{ $v('duration') }}" maxlength="100" placeholder="e.g. 4 years"></div>
        <div class="col-md-4"><label class="form-label fw-bold" for="fee">Fee</label><input type="text" class="form-control" id="fee" name="fee" value="{{ $v('fee') }}" maxlength="255" placeholder="e.g. ₹1,000 (General), free for SC/ST"></div>
        <div class="col-md-4"><label class="form-label fw-bold" for="salary">Salary / stipend</label><input type="text" class="form-control" id="salary" name="salary" value="{{ $v('salary') }}" maxlength="255" placeholder="e.g. ₹4–8 LPA starting"></div>
        <div class="col-md-4"><label class="form-label fw-bold" for="official_url">Official link</label><input type="text" class="form-control" id="official_url" name="official_url" value="{{ $v('official_url') }}" maxlength="500" placeholder="https://"></div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6" data-for="exam">
            <label class="form-label fw-bold" for="exam_id">Exam in the Exam module</label>
            <select class="form-select" id="exam_id" name="exam_id">
                <option value="">Not in the Exam module</option>
                @foreach($exams as $e)<option value="{{ $e->id }}" @selected((int) $v('exam_id') === $e->id)>{{ $e->short_name ?: $e->name }}{{ $e->short_name && $e->short_name !== $e->name ? ' — ' . $e->name : '' }}</option>@endforeach
            </select>
            <small class="text-muted">Links to its exam page on the website.</small>
        </div>
        <div class="col-md-6" data-for="course">
            <label class="form-label fw-bold" for="course_id">Master course</label>
            <select class="form-select" id="course_id" name="course_id">
                <option value="">Not a master course</option>
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected((int) $v('course_id') === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <small class="text-muted">The website then shows its duration, salary, colleges and fees.</small>
        </div>
        <div class="col-md-6" data-for="scholarship">
            <label class="form-label fw-bold" for="scholarship_id">Scholarship (Academics → Scholarships)</label>
            <select class="form-select" id="scholarship_id" name="scholarship_id">
                <option value="">Not in the Scholarship module</option>
                @foreach($scholarships as $s)<option value="{{ $s->id }}" @selected((int) $v('scholarship_id') === $s->id)>{{ $s->title }}</option>@endforeach
            </select>
            <small class="text-muted">Amount, eligibility and last date then come from the scholarship; the facts above are only needed when it is not linked.</small>
        </div>
        <div class="col-md-6" data-for="career">
            <label class="form-label fw-bold" for="career_id">Career</label>
            <select class="form-select" id="career_id" name="career_id">
                <option value="">Not in Careers</option>
                @foreach($careers as $c)<option value="{{ $c->id }}" @selected((int) $v('career_id') === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <small class="text-muted">Links to the career's page (route, exams, salary).</small>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3" data-for="roadmap"><div class="card-body p-4">
    <h5 class="mb-3">Web address and search engines</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold" for="url_slug">Web address</label>
            <div class="input-group"><span class="input-group-text" id="url-prefix">/career-roadmap/{class}/</span><input type="text" class="form-control" id="url_slug" name="url_slug" value="{{ $v('url_slug') }}" maxlength="120" placeholder="e.g. science-medical"></div>
            <small class="text-muted">Small letters, numbers and hyphens. Empty: made from the title. Changing it changes the page's address.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold" for="meta_title">SEO title</label>
            <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ $v('meta_title') }}" maxlength="255" placeholder="Empty: “{title} roadmap for {class}…”">
        </div>
        <div class="col-md-12">
            <label class="form-label fw-bold" for="meta_description">SEO description</label>
            <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="500">{{ $v('meta_description') }}</textarea>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
    <h5 class="mb-1">Buttons and other details</h5>
    <p class="text-muted small">A button shows only when it has a label and a link.</p>
    <div class="row g-3 mb-3">
        @foreach([1, 2] as $b)
            <div class="col-md-3"><label class="form-label small fw-bold">Button {{ $b }} label</label><input type="text" class="form-control" name="btn{{ $b }}_label" value="{{ old("btn{$b}_label", $cf["btn{$b}_label"] ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label small fw-bold">Button {{ $b }} link</label><input type="text" class="form-control" name="btn{{ $b }}_url" value="{{ old("btn{$b}_url", $cf["btn{$b}_url"] ?? '') }}" placeholder="/exam/jee-main or https://…"></div>
        @endforeach
    </div>
    <label class="form-label small fw-bold">Other details (name and value)</label>
    <div id="cf-rows">
        @foreach(old('custom_field_names') ? array_combine(range(0, count(old('custom_field_names')) - 1), old('custom_field_names')) : array_keys($other) as $i => $name)
            @php $val = old('custom_field_values') ? (old('custom_field_values')[$i] ?? '') : ($other[$name] ?? ''); @endphp
            <div class="row g-2 mb-2 cf-row"><div class="col-md-4"><input type="text" class="form-control" name="custom_field_names[]" value="{{ $name }}" placeholder="Name"></div><div class="col-md-7"><input type="text" class="form-control" name="custom_field_values[]" value="{{ is_array($val) ? json_encode($val) : $val }}" placeholder="Value"></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger cf-del" title="Remove">×</button></div></div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="cf-add"><i class="fas fa-plus"></i> Add a detail</button>
</div></div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="form-check form-switch m-0">
        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" @checked(old('status', $item->status ?? 1))>
        <label class="form-check-label" for="status">Show on the website</label>
    </div>
    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save</button>
</div></div>

@push('scripts')
<script>
    (function () {
        var $ = function (s) { return document.querySelector(s); }, $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };
        // show what applies: roadmap or item, a stream's details, the link for the item type
        function sync() {
            var top = !$('#parent_id').value, rt = $('#roadmap_type').value, it = $('#item_type').value;
            $$('[data-for]').forEach(function (el) {
                var f = el.getAttribute('data-for');
                el.hidden = f === 'roadmap' ? !top : f === 'item' ? top : f === 'stream' ? !(top && rt === 'stream') : (top || it !== f);
            });
            $('#roadmap_type').required = top; $('#item_type').required = !top;
            var cls = $('#stage_id'), opt = cls.options[cls.selectedIndex];
            $('#url-prefix').textContent = '/career-roadmap/' + (opt && opt.value ? opt.text.split(' — ')[0].toLowerCase().replace(/[^a-z0-9]+/g, '-') : '{class}') + '/';
        }
        // the "Under" list shows the picked class's roadmaps and items only
        function filterParents() {
            var cls = $('#stage_id').value, sel = $('#parent_id');
            Array.prototype.forEach.call(sel.options, function (o) { if (o.value) o.disabled = cls && o.getAttribute('data-stage') !== cls; });
            if (sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
            if (window.jQuery && jQuery(sel).data('select2')) jQuery(sel).trigger('change.select2');
        }
        ['#parent_id', '#roadmap_type', '#item_type', '#stage_id'].forEach(function (s) {
            $(s).addEventListener('change', sync);
            if (window.jQuery) jQuery(s).on('select2:select select2:clear', sync);
        });
        $('#stage_id').addEventListener('change', filterParents);
        if (window.jQuery) jQuery('#stage_id').on('select2:select', filterParents);
        // a parent picked from another class moves the class along
        if (window.jQuery) jQuery('#parent_id').on('select2:select', function () { var o = this.selectedOptions[0]; if (o && o.getAttribute('data-stage')) { jQuery('#stage_id').val(o.getAttribute('data-stage')).trigger('change'); } sync(); });
        document.addEventListener('click', function (e) {
            if (e.target.closest('#cf-add')) {
                var d = document.createElement('div'); d.className = 'row g-2 mb-2 cf-row';
                d.innerHTML = '<div class="col-md-4"><input type="text" class="form-control" name="custom_field_names[]" placeholder="Name"></div><div class="col-md-7"><input type="text" class="form-control" name="custom_field_values[]" placeholder="Value"></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger cf-del" title="Remove">×</button></div>';
                $('#cf-rows').appendChild(d);
            }
            var del = e.target.closest('.cf-del'); if (del) del.closest('.cf-row').remove();
        });
        filterParents(); sync();
    })();
</script>
@endpush
