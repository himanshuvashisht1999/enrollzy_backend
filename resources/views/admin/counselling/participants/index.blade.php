@extends('admin.layouts.master')

@section('title', 'Participating institutes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Participating institutes <span class="text-muted fs-6">{{ $cycle->counselling->name }} · {{ $cycle->title }}</span></h4>
        <p class="text-muted mb-0">College + course + quota taking part this year. {{ number_format($total) }} in total. Thousands of rows? Use the CSV import.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.counselling.cycles.show', $cycle) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Cycle</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'participants', 'cycle' => $cycle->id]) }}" class="btn btn-outline-primary"><i class="fas fa-file-csv me-1"></i> Import CSV</a>@endcan
        @can('cns-counsellings-edit')<button class="btn btn-primary" onclick="openParticipant()"><i class="fas fa-plus me-1"></i> Add</button>@endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search college, course or code"></div>
    <div class="col-md-3"><select name="quota" class="form-select custom-select2"><option value="">All quotas</option>@foreach($quotas as $q)<option value="{{ $q->id }}" @selected(request('quota') == $q->id)>{{ $q->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="institute_type" class="form-select custom-select2"><option value="">All types</option>@foreach(\App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES as $k => $v)<option value="{{ $k }}" @selected(request('institute_type') === $k)>{{ $v }}</option>@endforeach</select></div>
    <div class="col-md-1"><button class="btn btn-light border w-100">Go</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">College</th><th>Course</th><th>Quota</th><th>Type</th><th>Code</th><th class="text-end">Tuition / yr</th><th class="text-end">Seats</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($participants as $p)
            @php $payload = ['id' => $p->id, 'organisation_id' => $p->organisation_id, 'org_name' => $p->organisation->name ?? '', 'course_id' => $p->course_id, 'course_name' => $p->course->name ?? '',
                'quota_id' => $p->quota_id, 'institute_type' => $p->institute_type, 'institute_code' => $p->institute_code, 'annual_tuition_fee' => $p->annual_tuition_fee,
                'other_fees' => $p->other_fees, 'bond_details' => $p->bond_details, 'total_seats' => $p->total_seats]; @endphp
            <tr>
                <td class="ps-4">{{ $p->organisation->name ?? '(deleted #' . $p->organisation_id . ')' }}</td>
                <td>{{ $p->course->name ?? '(deleted #' . $p->course_id . ')' }}</td>
                <td>{{ $p->quota->name ?? '-' }}</td>
                <td>{{ \App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES[$p->institute_type] ?? '-' }}</td>
                <td class="small">{{ $p->institute_code ?: '-' }}</td>
                <td class="text-end">{{ $p->annual_tuition_fee !== null ? number_format($p->annual_tuition_fee) : '-' }}</td>
                <td class="text-end">{{ $p->total_seats ?? '-' }}</td>
                <td class="text-end pe-4 text-nowrap">
                    @can('cns-counsellings-edit')
                    <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openParticipant(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                    <form action="{{ route('admin.counselling.participants.destroy', $p) }}" method="POST" class="d-inline" data-confirm="Remove this participant?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">No participants yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $participants->links() }}</div>

@can('cns-counsellings-edit')
<div class="modal fade" id="participantModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="participantForm" method="POST" action="{{ route('admin.counselling.participants.store', $cycle) }}">
        @csrf <span id="pMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Participant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">College *</label><select name="organisation_id" id="p_organisation_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Course *</label><select name="course_id" id="p_course_id" class="form-select custom-select2" required></select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Quota *</label><select name="quota_id" id="p_quota_id" class="form-select custom-select2" required><option value="">Select</option>@foreach($quotas as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Institute type</label><select name="institute_type" id="p_institute_type" class="form-select custom-select2"><option value="">-</option>@foreach(\App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Authority code</label><input type="text" name="institute_code" id="p_institute_code" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Annual tuition fee</label><input type="number" step="any" min="0" name="annual_tuition_fee" id="p_annual_tuition_fee" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Other fees</label><input type="number" step="any" min="0" name="other_fees" id="p_other_fees" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Total seats</label><input type="number" min="0" name="total_seats" id="p_total_seats" class="form-control"></div>
            <div class="col-12"><label class="form-label fw-bold">Bond details</label><textarea name="bond_details" id="p_bond_details" rows="2" class="form-control"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endcan
@endsection

@push('scripts')
<script>
    const LOOKUP = @json(route('admin.counselling.imports.lookup'));
    const pStore = @json(route('admin.counselling.participants.store', $cycle));
    const pBase = @json(url('admin/counselling/participants'));
    function ajaxSelect(sel, type, parent) {
        $(sel).select2({ width: '100%', dropdownParent: parent, placeholder: 'Search...', minimumInputLength: 0,
            ajax: { url: LOOKUP, dataType: 'json', delay: 250, data: p => ({ q: p.term || '', type }), processResults: d => d } });
    }
    function openParticipant(row) {
        const modal = $('#participantModal'), form = document.getElementById('participantForm');
        form.reset();
        ['organisation_id', 'course_id'].forEach(f => { $('#p_' + f).empty(); });
        ajaxSelect('#p_organisation_id', 'organisation', modal);
        ajaxSelect('#p_course_id', 'course', modal);
        ['quota_id', 'institute_type'].forEach(f => $('#p_' + f).val('').trigger('change'));
        form.action = row ? pBase + '/' + row.id : pStore;
        $('#pMethod').html(row ? '<input type="hidden" name="_method" value="PUT">' : '');
        if (row) {
            $('#p_organisation_id').append(new Option(row.org_name, row.organisation_id, true, true)).trigger('change');
            $('#p_course_id').append(new Option(row.course_name, row.course_id, true, true)).trigger('change');
            ['quota_id', 'institute_type'].forEach(f => $('#p_' + f).val(row[f] ?? '').trigger('change'));
            ['institute_code', 'annual_tuition_fee', 'other_fees', 'total_seats', 'bond_details'].forEach(f => $('#p_' + f).val(row[f] ?? ''));
        }
        new bootstrap.Modal(document.getElementById('participantModal')).show();
    }
</script>
@endpush
