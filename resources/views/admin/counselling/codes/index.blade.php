@extends('admin.layouts.master')

@section('title', 'Institute codes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Institute codes</h4>
        <p class="text-muted mb-0">The code a counselling authority uses for a college. One college has a different code at MCC, JoSAA and each state. CSV imports match rows by these codes first.</p>
    </div>
    <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border">Counselling</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <form action="{{ route('admin.counselling.codes.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4"><label class="form-label fw-bold">College *</label>
            <select name="organisation_id" class="form-select" required><option value="">Select organisation</option>@foreach($organisations as $o)<option value="{{ $o->id }}" @selected(old('organisation_id') == $o->id)>{{ $o->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label fw-bold">Authority *</label>
            <select name="conducting_body_id" class="form-select" required><option value="">Select</option>@foreach($bodies as $b)<option value="{{ $b->id }}" @selected(old('conducting_body_id') == $b->id)>{{ $b->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label fw-bold">Code *</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div>
        <div class="col-md-1"><label class="form-label fw-bold">From</label><input type="number" name="valid_from_year" class="form-control" value="{{ old('valid_from_year') }}" placeholder="Year"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Add code</button></div>
    </form>
</div></div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search college or code"></div>
    <div class="col-md-4"><select name="body" class="form-select custom-select2"><option value="">All authorities</option>@foreach($bodies as $b)<option value="{{ $b->id }}" @selected(request('body') == $b->id)>{{ $b->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-light border w-100">Filter</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">College</th><th>Authority</th><th>Code</th><th>From year</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($codes as $c)
            <tr>
                <form id="ic{{ $c->id }}" action="{{ route('admin.counselling.codes.update', $c) }}" method="POST">@csrf @method('PUT')
                    <input type="hidden" name="organisation_id" value="{{ $c->organisation_id }}"><input type="hidden" name="conducting_body_id" value="{{ $c->conducting_body_id }}"></form>
                <td class="ps-4">{{ $c->organisation->name ?? '(deleted organisation #' . $c->organisation_id . ')' }}</td>
                <td>{{ $c->conductingBody->name ?? '-' }}</td>
                <td><input form="ic{{ $c->id }}" type="text" name="code" value="{{ $c->code }}" class="form-control form-control-sm" required></td>
                <td style="width:110px"><input form="ic{{ $c->id }}" type="number" name="valid_from_year" value="{{ $c->valid_from_year }}" class="form-control form-control-sm"></td>
                <td class="text-end pe-4 text-nowrap">
                    <button form="ic{{ $c->id }}" class="btn btn-sm btn-light border"><i class="fas fa-save text-primary"></i></button>
                    <form action="{{ route('admin.counselling.codes.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this code?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No codes yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $codes->links() }}</div>
@endsection
