{{-- "Identity" fields shared by the create and edit forms, laid out after Role (Category): the same details the website's
     "Become an expert" form asks for, so an approved application fills them. $expert is null on create. --}}
@php
    $v = fn ($k, $d = null) => old($k, $expert->{$k} ?? $d);
    $tagsOf = fn ($k) => array_values(array_filter((array) old($k, $expert->{$k} ?? []), fn ($t) => is_scalar($t) && trim((string) $t) !== ''));
    // the website form saves "male" / "female": match either case
    $gender = strtolower((string) $v('gender'));
    $dob = old('date_of_birth', $expert?->date_of_birth?->format('Y-m-d'));
@endphp
<div class="col-md-4">
    <label class="form-label">Designation / current role</label>
    <input type="text" class="form-control" name="designation" maxlength="150" list="designation-options" value="{{ $v('designation') }}" placeholder="e.g. Career counsellor">
    <datalist id="designation-options">@foreach(\App\Models\Expert::DESIGNATIONS as $d)<option value="{{ $d }}">@endforeach</datalist>
</div>
<div class="col-md-4">
    <label class="form-label">Gender</label>
    <select name="gender" class="form-select">
        <option value="">Select</option>
        @foreach(['Male', 'Female', 'Other'] as $g)<option value="{{ $g }}" @selected($gender === strtolower($g))>{{ $g }}</option>@endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label">Date of birth <small class="text-muted">(private)</small></label>
    <input type="date" class="form-control" name="date_of_birth" value="{{ $dob }}" max="{{ now()->subYears(16)->format('Y-m-d') }}">
</div>
<div class="col-md-8">
    <label class="form-label">Current organisation</label>
    <input type="text" class="form-control" name="current_institute_name" maxlength="200" value="{{ $v('current_institute_name') }}" placeholder="School, college, company or independent">
</div>
<div class="col-md-6">
    <label class="form-label">Subject specialization <small class="text-muted">(type and press Enter to add)</small></label>
    <select class="form-control select2-tags" name="subject_specialization[]" multiple>
        @foreach(collect($tagsOf('subject_specialization'))->merge(\App\Models\Expert::SUBJECTS)->unique() as $s)<option value="{{ $s }}" @selected(in_array($s, $tagsOf('subject_specialization'), true))>{{ $s }}</option>@endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label">Languages</label>
    <select class="form-control select2-tags" name="language_of_teaching[]" multiple>
        @foreach(collect($tagsOf('language_of_teaching'))->merge(\App\Models\Expert::LANGUAGES)->unique() as $l)<option value="{{ $l }}" @selected(in_array($l, $tagsOf('language_of_teaching'), true))>{{ $l }}</option>@endforeach
    </select>
</div>
