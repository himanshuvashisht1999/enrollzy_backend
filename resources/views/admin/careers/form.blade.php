@extends('admin.layouts.master')

@section('title', $career->exists ? 'Edit ' . $career->name : 'New career')

@php
    use App\Models\Career;
    $v = fn ($f, $d = null) => old($f, $career->{$f} ?? $d);
    $exams = old('exams', $career->exams ?? []);
    $courseIds = collect(old('course_ids', $career->course_ids ?? []))->map(fn ($x) => (int) $x)->all();
    $showFor = collect(old('show_for', $career->show_for ?? []))->map(fn ($x) => (int) $x)->all();
    $route = old('route', implode("\n", $career->route ?? []));
    $faqs = old('faqs', $career->faqs ?? []);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $career->exists ? $career->name : 'New career' }}</h4>
    <a href="{{ route('admin.careers.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to careers</a>
</div>

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ $career->exists ? route('admin.careers.update', $career->id) : route('admin.careers.store') }}">
    @csrf
    @if($career->exists) @method('PUT') @endif

    <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label fw-bold" for="name">Career <span class="text-danger">*</span></label><input class="form-control" id="name" name="name" value="{{ $v('name') }}" required maxlength="255" placeholder="e.g. Doctor"></div>
            <div class="col-md-3"><label class="form-label fw-bold" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id"><option value="">No category</option>@foreach($categories as $cc)<option value="{{ $cc->id }}" @selected((int) $v('category_id') === $cc->id)>{{ $cc->name }}</option>@endforeach</select>
                <small class="text-muted"><a href="{{ route('admin.career-categories.index') }}" target="_blank">Manage categories</a></small></div>
            <div class="col-md-2"><label class="form-label fw-bold" for="icon">Icon</label>
                <select class="form-select" id="icon" name="icon"><option value="">Default</option>@foreach(Career::ICONS as $k => $l)<option value="{{ $k }}" @selected($v('icon') === $k)>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label fw-bold" for="sort_order">Order</label><input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ $v('sort_order', 0) }}"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" @checked(old('status', $career->status ?? true))><label class="form-check-label" for="status">Show</label></div></div>
            <div class="col-md-12"><label class="form-label fw-bold" for="summary">One line</label><input class="form-control" id="summary" name="summary" value="{{ $v('summary') }}" maxlength="300" placeholder="e.g. Diagnoses and treats patients, from family doctor to surgeon."></div>
            <div class="col-md-12"><label class="form-label fw-bold" for="about">What they do</label><textarea class="form-control" id="about" name="about" rows="4">{{ $v('about') }}</textarea></div>
        </div>
    </div></div>

    <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
        <h5 class="mb-3">How to get there</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold" for="stream">Stream needed after Class 10</label><input class="form-control" id="stream" name="stream" value="{{ $v('stream') }}" maxlength="255" placeholder="e.g. PCB (Physics, Chemistry, Biology)"></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="salary">Salary</label><input class="form-control" id="salary" name="salary" value="{{ $v('salary') }}" maxlength="255" placeholder="e.g. About ₹8–15 LPA after MBBS"></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="route">The route, step by step</label><textarea class="form-control" id="route" name="route" rows="5" placeholder="One step per line, in order">{{ $route }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="recruiters">Where they work</label><textarea class="form-control" id="recruiters" name="recruiters" rows="5">{{ $v('recruiters') }}</textarea></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="exams">Exams</label>
                <select class="form-select custom-select2" id="exams" name="exams[]" multiple>@foreach($examNames->merge($exams)->unique() as $n)<option value="{{ $n }}" @selected(in_array($n, $exams, true))>{{ $n }}</option>@endforeach</select>
                <small class="text-muted">Pick from the list or type a new exam name and press Enter. Exams in the Exam module link to their page.</small></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="course_ids">Courses</label>
                <select class="form-select" id="course_ids" name="course_ids[]" multiple>@foreach($courses as $c)<option value="{{ $c->id }}" @selected(in_array($c->id, $courseIds, true))>{{ $c->name }}</option>@endforeach</select>
                <small class="text-muted">Master courses: the career page shows their colleges and fees.</small></div>
            <div class="col-md-12"><label class="form-label fw-bold" for="show_for">Popular career in these classes</label>
                <select class="form-select" id="show_for" name="show_for[]" multiple>@foreach($classes as $c)<option value="{{ $c->id }}" @selected(in_array($c->id, $showFor, true))>{{ $c->title }}</option>@endforeach</select>
                <small class="text-muted">The website lists it under "Popular careers" when a student picks one of these classes.</small></div>
        </div>
    </div></div>

    <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
        <h5 class="mb-1">Questions and answers</h5>
        <p class="text-muted small">Shown first in the career page's FAQ. The page adds questions made from the fields above (how to become one, stream, exams, salary, where they work), so add only what those do not answer.</p>
        <div id="faqs">
            @foreach($faqs as $i => $f)
                <div class="row g-2 mb-2 faq-row"><div class="col-md-4"><input class="form-control" name="faqs[{{ $i }}][q]" value="{{ $f['q'] ?? '' }}" placeholder="Question" maxlength="300"></div><div class="col-md-7"><textarea class="form-control" name="faqs[{{ $i }}][a]" rows="2" placeholder="Answer">{{ $f['a'] ?? '' }}</textarea></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger faq-del" title="Remove">×</button></div></div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="faq-add"><i class="fas fa-plus"></i> Add a question</button>
    </div></div>

    <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
        <h5 class="mb-3">Web address and search engines</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold" for="slug">Web address</label><div class="input-group"><span class="input-group-text">/career-roadmap/careers/</span><input class="form-control" id="slug" name="slug" value="{{ $v('slug') }}" maxlength="255" placeholder="made from the name"></div></div>
            <div class="col-md-6"><label class="form-label fw-bold" for="meta_title">SEO title</label><input class="form-control" id="meta_title" name="meta_title" value="{{ $v('meta_title') }}" maxlength="255"></div>
            <div class="col-md-12"><label class="form-label fw-bold" for="meta_description">SEO description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="500">{{ $v('meta_description') }}</textarea></div>
        </div>
    </div></div>

    <div class="d-flex justify-content-between">
        <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save</button>
    </div>
</form>
@if($career->exists)
    <form method="POST" action="{{ route('admin.careers.destroy', $career->id) }}" class="text-end" onsubmit="return confirm('Delete {{ addslashes($career->name) }}? Streams and items that link to it lose the link.')">@csrf @method('DELETE')<button class="btn btn-link text-danger"><i class="fas fa-trash me-1"></i> Delete</button></form>
@endif
@endsection

@push('scripts')
<script>
    // exams: pick or type a new one
    document.addEventListener('DOMContentLoaded', function () { if (window.jQuery) jQuery('#exams').select2({ width: '100%', tags: true, tokenSeparators: [','] }); });
    // questions: add and remove rows
    document.addEventListener('click', function (e) {
        if (e.target.closest('#faq-add')) {
            var i = Date.now(), d = document.createElement('div');
            d.className = 'row g-2 mb-2 faq-row';
            d.innerHTML = '<div class="col-md-4"><input class="form-control" name="faqs[' + i + '][q]" placeholder="Question" maxlength="300"></div><div class="col-md-7"><textarea class="form-control" name="faqs[' + i + '][a]" rows="2" placeholder="Answer"></textarea></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger faq-del" title="Remove">×</button></div>';
            document.getElementById('faqs').appendChild(d);
        }
        var del = e.target.closest('.faq-del'); if (del) del.closest('.faq-row').remove();
    });
</script>
@endpush
