@extends('admin.layouts.master')

@section('title', 'Exam Cities')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Cities</h4>
        <p class="text-muted mb-0">Master list used for exam centres, so the same city is never typed two ways.</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.index') }}" class="btn btn-light border">Exams</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <form action="{{ route('admin.exam-module.cities.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5"><label class="form-label fw-bold">City *</label><input type="text" name="name" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label fw-bold">State</label>
            <select name="state" class="form-select"><option value="">-</option>@foreach(\App\Models\ExamModule\Exam::STATES as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Add city</button></div>
    </form>
</div></div>

<form method="GET" class="mb-3"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search city or state"></form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">City</th><th>State</th><th>Centres</th><th>Active</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($cities as $c)
            <tr>
                <form id="ct{{ $c->id }}" action="{{ route('admin.exam-module.cities.update', $c) }}" method="POST">@csrf @method('PUT')</form>
                <td class="ps-4"><input form="ct{{ $c->id }}" type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm" required></td>
                <td><select form="ct{{ $c->id }}" name="state" class="form-select form-select-sm"><option value="">-</option>@foreach(\App\Models\ExamModule\Exam::STATES as $s)<option @selected($c->state === $s)>{{ $s }}</option>@endforeach</select></td>
                <td>{{ $usage[$c->id] ?? 0 }}</td>
                <td><div class="form-check form-switch"><input form="ct{{ $c->id }}" class="form-check-input" type="checkbox" name="status" value="1" @checked($c->status)></div></td>
                <td class="text-end pe-4 text-nowrap">
                    <button form="ct{{ $c->id }}" class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                    <form action="{{ route('admin.exam-module.cities.destroy', $c) }}" method="POST" class="d-inline" data-confirm="Delete this city?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No cities yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $cities->links() }}</div>
@endsection
