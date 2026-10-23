@extends('admin.layouts.master')

@php $isEdit = $counselling->exists; @endphp
@section('title', $isEdit ? 'Edit Counselling' : 'Add Counselling')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ $isEdit ? 'Edit ' . $counselling->name : 'Add Counselling' }}</h4>
    <a href="{{ $isEdit ? route('admin.counselling.counsellings.show', $counselling) : route('admin.counselling.counsellings.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

@if($fromExam)
    <div class="alert alert-info">This counselling will be mapped to <strong>{{ $fromExam->name }}</strong> as its primary exam.</div>
@endif

<form action="{{ $isEdit ? route('admin.counselling.counsellings.update', $counselling) : route('admin.counselling.counsellings.store') }}" method="POST" enctype="multipart/form-data">
    @csrf @if($isEdit) @method('PUT') @endif
    @if($fromExam)<input type="hidden" name="exam_id" value="{{ $fromExam->id }}">@endif
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
        <p class="text-muted small">Permanent identity only. Do not put the year in the name: "MCC NEET-UG Counselling", not "... 2026".</p>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $counselling->name) }}" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">Short Name</label>
                <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $counselling->short_name) }}"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $counselling->slug) }}" placeholder="auto from name">
                <div class="form-text">Public URL: /counselling/{slug}</div></div>
            <div class="col-12"><label class="form-label fw-bold">Aliases</label>
                <input type="text" name="aliases" class="form-control" value="{{ old('aliases', implode(', ', $counselling->aliases ?? [])) }}" placeholder="NEET UG counselling, AIQ counselling">
                <div class="form-text">Comma separated. Used for search.</div></div>

            <div class="col-md-4"><label class="form-label fw-bold">Conducting Body <span class="text-danger">*</span></label>
                <select name="conducting_organisation_id" class="form-select" required><option value="">Select</option>
                    @foreach($bodies as $b)<option value="{{ $b->id }}" @selected(old('conducting_organisation_id', $counselling->conducting_organisation_id) == $b->id)>{{ $b->name }}{{ ($b->short_name ?: $b->abbreviation) ? ' (' . ($b->short_name ?: $b->abbreviation) . ')' : '' }}</option>@endforeach</select>
                <div class="form-text">Organisations of type "Counselling Body". <a href="{{ route('admin.organisations.create', ['organisation_type_id' => \App\Models\Organisation::TYPE_COUNSELLING_BODY]) }}" target="_blank">Add one</a></div>
                @if(!$counselling->conducting_organisation_id && $counselling->legacyConductingBody)
                    <div class="form-text text-warning">Earlier set to "{{ $counselling->legacyConductingBody->name }}" (old list). Pick the matching organisation.</div>
                @endif</div>
            <div class="col-md-4"><label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                <select name="level" id="level" class="form-select custom-select2" required>
                    @foreach(\App\Models\Counselling\Counselling::LEVELS as $k => $v)<option value="{{ $k }}" @selected(old('level', $counselling->level) === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Mode</label>
                <select name="mode" class="form-select custom-select2">
                    @foreach(\App\Models\Counselling\Counselling::MODES as $k => $v)<option value="{{ $k }}" @selected(old('mode', $counselling->mode) === $k)>{{ $v }}</option>@endforeach</select></div>

            <div class="col-md-6" id="states_box"><label class="form-label fw-bold">States <span class="text-danger">*</span></label>
                <select name="states[]" class="form-select" multiple>
                    @foreach(\App\Models\ExamModule\Exam::STATES as $s)<option value="{{ $s }}" @selected(in_array($s, old('states', $counselling->states ?? [])))>{{ $s }}</option>@endforeach</select></div>
            <div class="col-md-6" id="owner_box"><label class="form-label fw-bold">Owning Organisation <span class="text-danger">*</span></label>
                <select name="owning_organisation_id" class="form-select"><option value="">Select</option>
                    @foreach($organisations as $o)<option value="{{ $o->id }}" @selected(old('owning_organisation_id', $counselling->owning_organisation_id) == $o->id)>{{ $o->name }}</option>@endforeach</select></div>

            <div class="col-md-6"><label class="form-label fw-bold">Program Levels</label>
                <select name="program_levels[]" class="form-select" multiple>
                    @foreach($levels as $l)<option value="{{ $l->id }}" @selected(in_array($l->id, array_map('intval', old('program_levels', $counselling->program_levels ?? []))))>{{ $l->title }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Course Categories</label>
                <select name="course_categories[]" class="form-select" multiple>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(in_array($c->id, array_map('intval', old('course_categories', $counselling->course_categories ?? []))))>{{ $c->title }}</option>@endforeach</select></div>

            <div class="col-md-6"><label class="form-label fw-bold">Official Website</label>
                <input type="url" name="official_website" class="form-control" value="{{ old('official_website', $counselling->official_website) }}" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Registration Portal URL</label>
                <input type="url" name="registration_portal_url" class="form-control" value="{{ old('registration_portal_url', $counselling->registration_portal_url) }}" placeholder="https://"></div>
            <div class="col-12"><label class="form-label fw-bold">Helpline</label>
                <input type="text" name="helpline" class="form-control" value="{{ old('helpline', $counselling->helpline) }}" placeholder="Phone / email"></div>

            <div class="col-md-6"><label class="form-label fw-bold">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                @if($counselling->logo)<img src="{{ asset('storage/' . $counselling->logo) }}" height="40" class="mt-2">@endif</div>
            <div class="col-md-6"><label class="form-label fw-bold">Cover Image</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
                @if($counselling->cover_image)<img src="{{ asset('storage/' . $counselling->cover_image) }}" height="60" class="mt-2 rounded">@endif</div>
            <div class="col-12"><label class="form-label fw-bold">About</label>
                <textarea name="about" rows="5" class="form-control">{{ old('about', $counselling->about) }}</textarea></div>

            <div class="col-md-3"><label class="form-label fw-bold">Sort Order</label>
                <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $counselling->sort_order ?? 0) }}"></div>
            <div class="col-md-5">
                @foreach(['is_active' => 'Active', 'is_featured' => 'Featured', 'is_archived' => 'Archived (discontinued, page stays live)'] as $f => $label)
                <div class="form-check form-switch">
                    <input type="hidden" name="{{ $f }}" value="0">
                    <input class="form-check-input" type="checkbox" name="{{ $f }}" id="{{ $f }}" value="1" @checked(old($f, $counselling->$f))>
                    <label class="form-check-label" for="{{ $f }}">{{ $label }}</label>
                </div>
                @endforeach
            </div>
            <div class="col-md-4"><label class="form-label fw-bold">Visibility</label>
                <select name="visibility" class="form-select custom-select2">
                    <option value="draft" @selected(old('visibility', $counselling->visibility) === 'draft')>Draft</option>
                    <option value="public" @selected(old('visibility', $counselling->visibility) === 'public') @cannot('cns-counsellings-publish') disabled @endcannot>Public</option>
                </select>
                <div class="form-text">New records start as Draft. Public needs the fields listed on the counselling page.</div></div>
        </div>
    </div></div>
    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Counselling</button>
</form>
@endsection

@push('scripts')
<script>
    function toggleLevelFields() {
        const level = $('#level').val();
        $('#states_box').toggle(level === 'state').find('select').prop('disabled', level !== 'state');
        const owner = ['university', 'institute'].includes(level);
        $('#owner_box').toggle(owner).find('select').prop('disabled', !owner);
    }
    $(function () { toggleLevelFields(); $('#level').on('change', toggleLevelFields); });
</script>
@endpush
