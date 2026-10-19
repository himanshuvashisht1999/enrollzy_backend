@extends('admin.layouts.master')

@section('title', 'Seat matrix v' . $version->version_no)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Seat matrix v{{ $version->version_no }}
            @if($version->is_live)<span class="badge bg-success fs-6 align-middle">Live</span>@endif
            <span class="text-muted fs-6">{{ $version->round->cycle->counselling->name }} · {{ $version->round->name }}</span></h4>
        <p class="text-muted mb-0">{{ $version->label }} · {{ number_format($version->row_count) }} rows · {{ number_format($totalSeats) }} seats</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.seats.index', $version->round) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Versions</a>
        @if($version->version_no > 1)<a href="{{ route('admin.counselling.seats.diff', $version) }}" class="btn btn-outline-secondary">Compare with previous</a>@endif
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search college or course"></div>
    <div class="col-md-3"><select name="quota" class="form-select custom-select2"><option value="">All quotas</option>@foreach($quotas as $q)<option value="{{ $q->id }}" @selected(request('quota') == $q->id)>{{ $q->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="category" class="form-select custom-select2"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-1"><button class="btn btn-light border w-100">Go</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">College</th><th>Course</th><th>Quota</th><th>Category</th><th>Gender</th><th>PwD</th><th class="text-end pe-4">Seats</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td class="ps-4">{{ $r->organisation->name ?? '#' . $r->organisation_id }}</td>
                <td>{{ $r->course->name ?? '#' . $r->course_id }}</td>
                <td>{{ $r->quota->name ?? '-' }}</td>
                <td>{{ $r->category->name ?? '-' }}</td>
                <td>{{ $r->gender ? ucfirst($r->gender) : 'Any' }}</td>
                <td>{{ $r->pwd ? 'Yes' : '-' }}</td>
                <td class="text-end pe-4 fw-semibold">{{ $r->seats }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No rows match.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $rows->links() }}</div>
@endsection
