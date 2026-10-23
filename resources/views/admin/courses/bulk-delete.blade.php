@extends('admin.layouts.master')

@section('title', 'Delete courses')

@section('content')
<div class="mb-4">
    <a href="{{ $back }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Back to courses</a>
    <h3 class="fw-bold mt-2">Delete {{ number_format($courses->count()) }} course{{ $courses->count() === 1 ? '' : 's' }}?</h3>
    <p class="text-muted mb-0">Nothing has been deleted yet. Check the list and what goes with it, then confirm at the bottom.</p>
</div>

@if($linked)
    <div class="alert alert-danger">
        <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> These are deleted together with the courses and cannot be brought back:</div>
        <ul class="mb-0">
            @foreach($linked as $label => $n)<li><strong>{{ number_format($n) }}</strong> {{ $label }}</li>@endforeach
        </ul>
    </div>
@else
    <div class="alert alert-info">None of these courses is used by a college, an admission route, a scholarship or a faculty member. Only the courses themselves are deleted.</div>
@endif

<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <h6 class="fw-bold">Courses to delete <span class="badge bg-secondary">{{ number_format($courses->count()) }}</span></h6>
    <div class="row g-1 small" style="max-height: 360px; overflow-y: auto">
        @foreach($courses->take(300) as $c)
            <div class="col-md-6 col-lg-4">{{ $c->name }}@if($c->full_form && $c->full_form !== $c->name) <span class="text-muted">({{ \Illuminate\Support\Str::limit($c->full_form, 60) }})</span>@endif <span class="text-muted">#{{ $c->id }}</span></div>
        @endforeach
    </div>
    @if($courses->count() > 300)<div class="small text-muted mt-2">… and {{ number_format($courses->count() - 300) }} more.</div>@endif
</div></div>

<form method="POST" action="{{ route('admin.courses.bulk-destroy') }}" class="d-flex gap-2 align-items-center flex-wrap">
    @csrf
    <input type="hidden" name="ids" value="{{ $courses->pluck('id')->implode(',') }}">
    <input type="hidden" name="back" value="{{ $back }}">
    <a href="{{ $back }}" class="btn btn-light border">Cancel, keep them</a>
    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Yes, delete {{ number_format($courses->count()) }} course{{ $courses->count() === 1 ? '' : 's' }}{{ $linked ? ' and their linked records' : '' }}</button>
</form>
@endsection
