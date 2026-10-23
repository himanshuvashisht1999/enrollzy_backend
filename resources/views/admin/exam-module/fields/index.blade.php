@extends('admin.layouts.master')

@section('title', 'Schema Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Schema Builder <span class="text-muted fs-6">{{ $exam->name }}</span></h4>
        <p class="text-muted mb-0">Extra details unique to this exam. Eligibility, fees, dates, pattern and syllabus have their own structured tabs and cannot be created here.</p>
    </div>
    <a href="{{ route('admin.exam-module.exams.show', $exam) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">Add a field</h6>
    <form action="{{ route('admin.exam-module.fields.store', $exam) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-4"><label class="form-label fw-bold">Label *</label><input type="text" name="label" class="form-control" value="{{ old('label') }}" required></div>
        <div class="col-md-3"><label class="form-label fw-bold">Key</label><input type="text" name="key" class="form-control" value="{{ old('key') }}" placeholder="auto from label">
            <div class="form-text">Never changes once created.</div></div>
        <div class="col-md-3"><label class="form-label fw-bold">Type *</label>
            <select name="type" class="form-select" id="newType">@foreach(\App\Models\ExamModule\CustomField::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label fw-bold">Applies to</label>
            <select name="scope" class="form-select"><option value="exam" @selected(old('scope') === 'exam')>Whole exam</option><option value="cycle" @selected(old('scope') === 'cycle')>Each cycle (copied yearly)</option></select></div>
        <div class="col-12" id="newOptions" style="display:none"><label class="form-label fw-bold">Options / group columns</label>
            <textarea name="options" rows="3" class="form-control" placeholder="One per line">{{ old('options') }}</textarea>
            <div class="form-text">Dropdown and multi-select: the choices. Repeatable group: the column names.</div></div>
        <div class="col-12"><button class="btn btn-primary">Add field</button></div>
    </form>
</div></div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Label</th><th>Key</th><th>Type</th><th>Applies to</th><th>Options</th><th>Order</th><th>Values</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($fields as $f)
            <tr>
                <form id="uf{{ $f->id }}" action="{{ route('admin.exam-module.fields.update', $f) }}" method="POST">@csrf @method('PUT')</form>
                <td class="ps-4"><input form="uf{{ $f->id }}" type="text" name="label" value="{{ $f->label }}" class="form-control form-control-sm" required></td>
                <td><code>{{ $f->key }}</code></td>
                <td>{{ \App\Models\ExamModule\CustomField::TYPES[$f->type] }}</td>
                <td>{{ $f->scope === 'cycle' ? 'Each cycle' : 'Whole exam' }}</td>
                <td>@if($f->options)<textarea form="uf{{ $f->id }}" name="options" rows="2" class="form-control form-control-sm">{{ implode("\n", $f->options) }}</textarea>@else - @endif</td>
                <td style="width:80px"><input form="uf{{ $f->id }}" type="number" min="0" name="sort_order" value="{{ $f->sort_order }}" class="form-control form-control-sm"></td>
                <td>{{ $f->values_count }}</td>
                <td class="text-end pe-4 text-nowrap">
                    <button form="uf{{ $f->id }}" class="btn btn-sm btn-light border" title="Save"><i class="fas fa-save text-primary"></i></button>
                    <form action="{{ route('admin.exam-module.fields.destroy', $f) }}" method="POST" class="d-inline"
                          data-confirm="{{ $f->values_count ? 'This field has saved data. Delete it anyway? The values are kept but hidden.' : 'Delete this field?' }}">
                        @csrf @method('DELETE')<input type="hidden" name="confirm" value="1">
                        <button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">No extra fields yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection

@push('scripts')
<script>
    function syncOptions() { $('#newOptions').toggle(['dropdown', 'multi_select', 'group'].includes($('#newType').val())); }
    $(function () { syncOptions(); $('#newType').on('change', syncOptions); });
</script>
@endpush
