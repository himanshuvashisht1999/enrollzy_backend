@extends('admin.layouts.master')

@php $isEdit = $exam->exists; @endphp
@section('title', $isEdit ? 'Edit Exam' : 'Add Exam')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ $isEdit ? 'Edit ' . $exam->name : 'Add Exam' }}</h4>
    <a href="{{ $isEdit ? route('admin.exam-module.exams.show', $exam) : route('admin.exam-module.exams.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<form action="{{ $isEdit ? route('admin.exam-module.exams.update', $exam) : route('admin.exam-module.exams.store') }}" method="POST" enctype="multipart/form-data">
    @csrf @if($isEdit) @method('PUT') @endif
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <p class="text-muted small">Permanent identity only. Registration, results, fees and dates are managed per cycle.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $exam->name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Short Name</label>
                    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $exam->short_name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $exam->slug) }}" placeholder="auto from name">
                    <div class="form-text">Public URL: /exams/{slug}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Aliases</label>
                    <input type="text" name="aliases" class="form-control" value="{{ old('aliases', implode(', ', $exam->aliases ?? [])) }}" placeholder="NEET, NEET UG, National Eligibility cum Entrance Test">
                    <div class="form-text">Comma separated. Used for search.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Source Type <span class="text-danger">*</span></label>
                    <select name="source_type" id="source_type" class="form-select custom-select2" required>
                        <option value="external" @selected(old('source_type', $exam->source_type) === 'external')>External (national/state/international body)</option>
                        <option value="internal" @selected(old('source_type', $exam->source_type) === 'internal')>Internal (a college/organisation)</option>
                    </select>
                </div>
                <div class="col-md-4" id="owner_box">
                    <label class="form-label fw-bold">Owning Organisation <span class="text-danger">*</span></label>
                    <select name="owning_organisation_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($organisations as $o)<option value="{{ $o->id }}" @selected(old('owning_organisation_id', $exam->owning_organisation_id) == $o->id)>{{ $o->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4" id="body_box">
                    <label class="form-label fw-bold">Conducting Body</label>
                    <select name="conducting_body_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($bodies as $b)<option value="{{ $b->id }}" @selected(old('conducting_body_id', $exam->conducting_body_id) == $b->id)>{{ $b->name }}</option>@endforeach
                    </select>
                    @can('exm-conducting-bodies-add')<div class="form-text"><a href="{{ route('admin.exam-module.conducting-bodies.create') }}" target="_blank">Add a new body</a></div>@endcan
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Exam Type</label>
                    <select name="exam_type" id="exam_type" class="form-select custom-select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\ExamModule\Exam::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('exam_type', $exam->exam_type) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Exam Frequency</label>
                    <select name="frequency" class="form-select custom-select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\ExamModule\Exam::FREQUENCIES as $k => $v)<option value="{{ $k }}" @selected(old('frequency', $exam->frequency) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4" id="states_box">
                    <label class="form-label fw-bold">States</label>
                    <select name="states[]" class="form-select" multiple>
                        @foreach(\App\Models\ExamModule\Exam::STATES as $s)<option value="{{ $s }}" @selected(in_array($s, old('states', $exam->states ?? [])))>{{ $s }}</option>@endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Exam Categories</label>
                    <select name="exam_categories[]" class="form-select" multiple>
                        @foreach($categories as $c)<option value="{{ $c->id }}" @selected(in_array($c->id, array_map('intval', old('exam_categories', $exam->exam_categories ?? []))))>{{ $c->title }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Program Levels</label>
                    <select name="program_levels[]" class="form-select" multiple>
                        @foreach($levels as $l)<option value="{{ $l->id }}" @selected(in_array($l->id, array_map('intval', old('program_levels', $exam->program_levels ?? []))))>{{ $l->title }}</option>@endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Feeder Exam</label>
                    <select name="feeder_exam_id" class="form-select">
                        <option value="">None</option>
                        @foreach($feederExams as $f)<option value="{{ $f->id }}" @selected(old('feeder_exam_id', $exam->feeder_exam_id) == $f->id)>{{ $f->name }}</option>@endforeach
                    </select>
                    <div class="form-text">E.g. JEE Advanced requires JEE Main.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Languages</label>
                    <input type="text" name="languages" class="form-control" value="{{ old('languages', implode(', ', $exam->languages ?? [])) }}" placeholder="English, Hindi, Tamil">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Official Website</label>
                    <input type="url" name="official_website" class="form-control" value="{{ old('official_website', $exam->official_website) }}" placeholder="https://">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if($exam->logo)<img src="{{ asset('storage/' . $exam->logo) }}" height="40" class="mt-2">@endif
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">About</label>
                    <textarea name="about" rows="5" class="form-control">{{ old('about', $exam->about) }}</textarea>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="has_stages" value="0">
                        <input class="form-check-input" type="checkbox" name="has_stages" id="has_stages" value="1" @checked(old('has_stages', $exam->has_stages))>
                        <label class="form-check-label" for="has_stages">Does this exam have stages?</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', $exam->status))>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Visibility</label>
                    <select name="visibility" class="form-select custom-select2">
                        <option value="draft" @selected(old('visibility', $exam->visibility) === 'draft')>Draft</option>
                        <option value="public" @selected(old('visibility', $exam->visibility) === 'public') @cannot('exm-exams-publish') disabled @endcannot>Public</option>
                    </select>
                    <div class="form-text">Public needs slug, type, category, conducting body (or owner), program level, about and website.</div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Exam</button>
</form>
@endsection

@push('scripts')
<script>
    function toggleExamFields() {
        const internal = $('#source_type').val() === 'internal';
        $('#owner_box').toggle(internal).find('select').prop('disabled', !internal);
        $('#body_box').toggle(!internal);
        $('#states_box').toggle($('#exam_type').val() === 'state');
    }
    $(function () { toggleExamFields(); $('#source_type, #exam_type').on('change', toggleExamFields); });
</script>
@endpush
