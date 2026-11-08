@extends('admin.layouts.master')

@section('title', 'Documents: ' . $expert->name)

@php
    use App\Models\ExpertDocument as D;
    $badge = fn ($s) => match ($s) { 'verified' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger', default => 'bg-warning-subtle text-warning-emphasis' };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Documents · {{ $expert->name }}</h4>
        <p class="text-muted mb-0">Qualifications, certificates, awards, achievements and ID. Files open only for staff here and for the expert on their dashboard. Verified documents marked "On profile" are listed on the public profile as verified credentials (title, issuer, year; never the file, never an ID).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('experts.edit', $expert) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Expert</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Add a document <span class="text-muted fw-normal small">(you checked it: saved as verified)</span></h6>
    <form action="{{ route('experts.documents.store', $expert) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-2"><label class="form-label small mb-1">Type</label><select name="type" class="form-select form-select-sm" required>@foreach(D::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small mb-1">Title</label><input type="text" name="title" maxlength="200" class="form-control form-control-sm" placeholder="e.g. M.Sc. Psychology" required></div>
        <div class="col-md-2"><label class="form-label small mb-1">Issued by</label><input type="text" name="issued_by" maxlength="200" class="form-control form-control-sm" placeholder="University, body"></div>
        <div class="col-md-1"><label class="form-label small mb-1">Year</label><input type="number" name="year" min="1950" max="{{ date('Y') + 1 }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><label class="form-label small mb-1">File (PDF or image, 5 MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="form-control form-control-sm" required></div>
        <div class="col-md-1"><div class="form-check small"><input class="form-check-input" type="checkbox" name="show_on_profile" value="1" id="spnew" checked><label class="form-check-label" for="spnew">On profile</label></div></div>
        <div class="col-md-1"><button class="btn btn-sm btn-primary w-100">Add</button></div>
    </form>
</div></div>

<div class="card border-0 shadow-sm rounded-4"><div class="card-body">
    @forelse($docs as $d)
        @php $tone = ['verified' => 'success', 'rejected' => 'danger'][$d->status] ?? 'warning'; @endphp
        {{-- the card takes its status colour: amber pending, green verified, red rejected --}}
        <div class="rounded-3 p-3 mb-3 shadow-sm" style="border: 1px solid var(--bs-{{ $tone }}-border-subtle); border-left: 5px solid var(--bs-{{ $tone }}); background: #fff;">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <span class="badge bg-light text-dark border">{{ D::TYPES[$d->type] ?? $d->type }}</span>
                    <span class="fw-semibold ms-1">{{ $d->title }}</span>
                    <div class="small text-muted">{{ collect([$d->issued_by, $d->year])->filter()->implode(', ') }} · uploaded by {{ $d->uploaded_by === 'staff' ? 'staff' : 'the expert' }} {{ $d->created_at->format('d M Y') }}</div>
                </div>
                <div class="text-nowrap">
                    <span class="badge {{ $badge($d->status) }}">{{ D::STATUSES[$d->status] }}</span>
                    @if($d->isPublic())<span class="badge bg-primary-subtle text-primary">On profile</span>@endif
                    <a href="{{ route('expert-documents.file', $d) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border ms-1"><i class="fas fa-external-link-alt me-1"></i>Open</a>
                    <form action="{{ route('expert-documents.destroy', $d) }}" method="POST" class="d-inline" data-confirm="Delete this document and its file?">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                </div>
            </div>
            <form action="{{ route('expert-documents.review', $d) }}" method="POST" class="row g-2 align-items-center mt-2">
                @csrf
                <div class="col-md-2"><select name="status" class="form-select form-select-sm">@foreach(D::STATUSES as $k => $v)<option value="{{ $k }}" @selected($d->status === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="col-md-6"><input type="text" name="review_note" value="{{ $d->review_note }}" maxlength="500" class="form-control form-control-sm" placeholder="Note to the expert (required to reject: what is wrong)"></div>
                <div class="col-md-2">@if($d->type !== 'id_proof')<div class="form-check small"><input class="form-check-input" type="checkbox" name="show_on_profile" value="1" id="sp{{ $d->id }}" @checked($d->show_on_profile)><label class="form-check-label" for="sp{{ $d->id }}">On profile</label></div>@else<span class="small text-muted">Never public</span>@endif</div>
                <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Save</button></div>
            </form>
        </div>
    @empty
        <p class="text-muted mb-0">No documents yet. The expert can upload them from their dashboard on the website, or add them above.</p>
    @endforelse
</div></div>
@endsection
