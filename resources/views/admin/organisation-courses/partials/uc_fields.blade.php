{{-- University / college course, "Course" tab: how it is taught here (mode, seats, status) and the optional extras.
     The course itself (name, level, stream, duration) comes from the master course.
     $oc: the organisation course on edit, null on create. Needs $specializations, $languages, $typeId. --}}
@php
    $v = fn ($k, $d = null) => old($k, $oc ? $oc->$k : $d);
    $list = function ($k) use ($oc) {
        $x = old($k, $oc ? ($oc->$k ?? []) : []);
        return is_array($x) ? $x : (json_decode((string) $x, true) ?: []);
    };
    $selectedSpecs = $list('specialization_ids');
    $selectedLanguages = $list('course_languages');
    $modes = ['Regular', 'Full-time', 'Part-time', 'Online', 'Distance', 'Hybrid'];
    $mode = (string) $v('mode', 'Regular');
@endphp

<div class="col-md-4">
    <label class="form-label fw-bold">Mode</label>
    <select name="mode" class="form-select" style="width:100%">
        @foreach($modes as $m)<option value="{{ $m }}" @selected($mode === $m)>{{ $m }}</option>@endforeach
        {{-- a value saved before the list existed ("Full-Time (Regular)"...) stays selectable --}}
        @if($mode !== '' && !in_array($mode, $modes, true))<option value="{{ $mode }}" selected>{{ $mode }}</option>@endif
    </select>
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Seats (Intake)</label>
    <input type="text" name="student_strength" class="form-control" value="{{ $v('student_strength') }}" placeholder="e.g. 60" inputmode="numeric">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Status</label>
    <div class="form-check form-switch mt-2">
        <input type="hidden" name="status" value="0">
        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked($oc ? old('status', $oc->status) : true)>
        <label class="form-check-label" for="status">Active</label>
    </div>
</div>

<div class="col-md-6">
    <label class="form-label fw-bold">Specialization Area (Multiple)</label>
    <select name="specialization_ids[]" class="form-select select2" multiple>
        @foreach($specializations as $spec)
            <option value="{{ $spec->id }}" @selected(in_array($spec->id, $selectedSpecs))>{{ $spec->title }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label fw-bold">Course Languages (Multiple)</label>
    <select name="course_languages[]" class="form-select select2" multiple>
        @foreach($languages as $lang)
            <option value="{{ $lang->id }}" @selected(in_array($lang->id, $selectedLanguages))>{{ $lang->title }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="form-label fw-bold">Rating (0-5)</label>
    <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ $v('rating') }}">
</div>
<div class="col-md-3">
    <label class="form-label fw-bold">ROI</label>
    <select name="roi" class="form-select" style="width:100%">
        <option value="">Select ROI</option>
        @foreach(['Low', 'Medium', 'High'] as $r)<option value="{{ $r }}" @selected($v('roi') == $r)>{{ $r }}</option>@endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="form-label fw-bold">Display Order</label>
    <input type="number" name="sort_order" class="form-control" value="{{ $v('sort_order', 0) }}">
</div>
@include('admin.organisation-courses.partials.extra-fields', ['only' => ['verified_reviews_only'], 'bare' => true])
