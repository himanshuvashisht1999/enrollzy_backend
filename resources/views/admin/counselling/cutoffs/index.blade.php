@extends('admin.layouts.master')

@section('title', 'Cutoffs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Cutoffs <span class="text-muted fs-6">{{ $round->cycle->counselling->name }} · {{ $round->cycle->title }} · {{ $round->name }}</span></h4>
        <p class="text-muted mb-0">Opening and closing value per college, course, quota and category for this round. {{ number_format($total) }} in total.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.rounds.show', $round) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Round</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'cutoffs', 'cycle' => $round->cycle_id, 'round' => $round->id]) }}" class="btn btn-outline-primary"><i class="fas fa-file-csv me-1"></i> Import CSV</a>@endcan
        @can('cns-counsellings-delete')
            @if($total)<form action="{{ route('admin.counselling.cutoffs.clear', $round) }}" method="POST" onsubmit="return confirm('Delete ALL {{ $total }} cutoffs of this round? This cannot be undone.')">@csrf<button class="btn btn-outline-danger">Delete all</button></form>@endif
        @endcan
        @can('cns-counsellings-edit')<button class="btn btn-primary" onclick="openCutoff()"><i class="fas fa-plus me-1"></i> Add</button>@endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search college or course"></div>
    <div class="col-md-2"><select name="exam" class="form-select custom-select2"><option value="">All exams</option>@foreach($exams as $e)<option value="{{ $e->id }}" @selected(request('exam') == $e->id)>{{ $e->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="quota" class="form-select custom-select2"><option value="">All quotas</option>@foreach($quotas as $q)<option value="{{ $q->id }}" @selected(request('quota') == $q->id)>{{ $q->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="category" class="form-select custom-select2"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="gender" class="form-select custom-select2"><option value="">Any gender</option><option value="neutral" @selected(request('gender') === 'neutral')>Gender-neutral</option><option value="female" @selected(request('gender') === 'female')>Female-only</option><option value="male" @selected(request('gender') === 'male')>Male-only</option></select></div>
    <div class="col-md-1"><button class="btn btn-light border w-100">Go</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">College</th><th>Course</th><th>Exam</th><th>Quota</th><th>Category</th><th>Type</th><th class="text-end">Opening</th><th class="text-end">Closing</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($cutoffs as $c)
            @php $payload = ['id' => $c->id, 'exam_id' => $c->exam_id, 'organisation_id' => $c->organisation_id, 'org_name' => $c->organisation->name ?? '', 'course_id' => $c->course_id,
                'course_name' => $c->course->name ?? '', 'quota_id' => $c->quota_id, 'category_id' => $c->category_id, 'gender' => $c->gender, 'pwd' => $c->pwd,
                'rank_type' => $c->rank_type, 'opening_value' => $c->opening_value !== null ? (float) $c->opening_value : null, 'closing_value' => $c->closing_value !== null ? (float) $c->closing_value : null]; @endphp
            <tr>
                <td class="ps-4">{{ $c->organisation->name ?? '#' . $c->organisation_id }}</td>
                <td>{{ $c->course->name ?? '#' . $c->course_id }}</td>
                <td class="small">{{ $c->exam->name ?? '-' }}</td>
                <td>{{ $c->quota->name ?? '-' }}</td>
                <td>{{ $c->category->name ?? '-' }}{{ $c->pwd ? ' (PwD)' : '' }}{{ $c->gender ? ' · ' . ucfirst($c->gender) . ' only' : '' }}</td>
                <td class="small">{{ \App\Models\Counselling\Cutoff::RANK_TYPES[$c->rank_type] ?? $c->rank_type }}</td>
                <td class="text-end">{{ $c->opening_value !== null ? number_format((float) $c->opening_value, 0) : '-' }}</td>
                <td class="text-end fw-semibold">{{ $c->closing_value !== null ? number_format((float) $c->closing_value, 0) : '-' }}</td>
                <td class="text-end pe-4 text-nowrap">
                    @can('cns-counsellings-edit')
                    <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openCutoff(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                    <form action="{{ route('admin.counselling.cutoffs.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cutoff?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-5">No cutoffs yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $cutoffs->links() }}</div>

@can('cns-counsellings-edit')
<div class="modal fade" id="cutoffModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="cutoffForm" method="POST" action="{{ route('admin.counselling.cutoffs.store', $round) }}">
        @csrf <span id="cMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Cutoff</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Exam *</label><select name="exam_id" id="c_exam_id" class="form-select custom-select2" required>@foreach($exams as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
                <div class="form-text">Only exams mapped to this counselling.</div></div>
            <div class="col-md-6"><label class="form-label fw-bold">Rank type *</label><select name="rank_type" id="c_rank_type" class="form-select custom-select2" required>@foreach(\App\Models\Counselling\Cutoff::RANK_TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">College *</label><select name="organisation_id" id="c_organisation_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Course *</label><select name="course_id" id="c_course_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Quota *</label><select name="quota_id" id="c_quota_id" class="form-select custom-select2" required>@foreach($quotas as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Category *</label><select name="category_id" id="c_category_id" class="form-select custom-select2" required>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label fw-bold">Gender</label><select name="gender" id="c_gender" class="form-select custom-select2"><option value="">Neutral</option><option value="female">Female</option><option value="male">Male</option></select></div>
            <div class="col-md-2"><div class="form-check form-switch mt-4"><input type="hidden" name="pwd" value="0"><input class="form-check-input" type="checkbox" name="pwd" id="c_pwd" value="1"><label class="form-check-label">PwD</label></div></div>
            <div class="col-md-6"><label class="form-label fw-bold">Opening value</label><input type="number" step="any" min="0" name="opening_value" id="c_opening_value" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Closing value *</label><input type="number" step="any" min="0" name="closing_value" id="c_closing_value" class="form-control" required></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endcan
@endsection

@push('scripts')
<script>
    const LOOKUP = @json(route('admin.counselling.imports.lookup'));
    const cStore = @json(route('admin.counselling.cutoffs.store', $round));
    const cBase = @json(url('admin/counselling/cutoffs'));
    function ajaxSelect(sel, type, parent) {
        $(sel).select2({ width: '100%', dropdownParent: parent, placeholder: 'Search...',
            ajax: { url: LOOKUP, dataType: 'json', delay: 250, data: p => ({ q: p.term || '', type }), processResults: d => d } });
    }
    function openCutoff(row) {
        const modal = $('#cutoffModal'), form = document.getElementById('cutoffForm');
        form.reset();
        $('#c_organisation_id, #c_course_id').empty();
        ajaxSelect('#c_organisation_id', 'organisation', modal);
        ajaxSelect('#c_course_id', 'course', modal);
        form.action = row ? cBase + '/' + row.id : cStore;
        $('#cMethod').html(row ? '<input type="hidden" name="_method" value="PUT">' : '');
        ['exam_id', 'rank_type', 'quota_id', 'category_id', 'gender'].forEach(f => $('#c_' + f).val(row ? (row[f] ?? '') : (f === 'rank_type' ? 'air' : $('#c_' + f + ' option:first').val())).trigger('change'));
        $('#c_pwd').prop('checked', !!(row && row.pwd));
        $('#c_opening_value').val(row ? (row.opening_value ?? '') : '');
        $('#c_closing_value').val(row ? (row.closing_value ?? '') : '');
        if (row) {
            $('#c_organisation_id').append(new Option(row.org_name, row.organisation_id, true, true)).trigger('change');
            $('#c_course_id').append(new Option(row.course_name, row.course_id, true, true)).trigger('change');
        }
        new bootstrap.Modal(document.getElementById('cutoffModal')).show();
    }
</script>
@endpush
