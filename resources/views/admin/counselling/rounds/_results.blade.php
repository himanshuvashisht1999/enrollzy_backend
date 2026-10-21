{{-- Seat matrix + cutoffs summary and allotment results for one round. Needs: $round, $cycle --}}
@php
    $live = $round->liveSeatMatrix;
    $versionCount = $round->seatMatrixVersions()->count();
    $cutoffCount = $round->cutoffs()->count();
    $results = $round->results()->get();
    $choiceOpen = $round->openChoiceFilling();
@endphp

<div class="row g-4 mb-4">
    <div class="col-md-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
        <h6 class="fw-bold">Seat matrix</h6>
        @if($live)
            <p class="mb-1"><span class="badge bg-success">Live</span> v{{ $live->version_no }} · {{ $live->label ?: 'unlabelled' }}</p>
            <p class="small text-muted mb-2">{{ number_format($live->row_count) }} rows · {{ $versionCount }} version{{ $versionCount === 1 ? '' : 's' }}</p>
        @elseif($versionCount)
            <p class="mb-2"><span class="badge bg-warning text-dark">No live version</span> <span class="small text-muted">{{ $versionCount }} version(s) exist</span></p>
        @else
            <p class="text-muted small mb-2">No seat matrix yet.</p>
        @endif
        <a href="{{ route('admin.counselling.seats.index', $round) }}" class="btn btn-sm btn-light border">Versions</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'seats', 'cycle' => $cycle->id, 'round' => $round->id]) }}" class="btn btn-sm btn-outline-primary">Import CSV</a>@endcan
    </div></div></div>
    <div class="col-md-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
        <h6 class="fw-bold">Cutoffs</h6>
        <p class="mb-2">{{ number_format($cutoffCount) }} cutoff{{ $cutoffCount === 1 ? '' : 's' }} in this round</p>
        <a href="{{ route('admin.counselling.cutoffs.index', $round) }}" class="btn btn-sm btn-light border">View</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.create', ['type' => 'cutoffs', 'cycle' => $cycle->id, 'round' => $round->id]) }}" class="btn btn-sm btn-outline-primary">Import CSV</a>@endcan
    </div></div></div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Allotment results</h6>
        @can('cns-counsellings-edit')<button class="btn btn-sm btn-primary" onclick="openResult()"><i class="fas fa-plus me-1"></i> Publish result</button>@endcan
    </div>
    <p class="small text-muted">Publishing a result also sets this round's <em>Allotment result</em> date to Declared, in one step.</p>
    @forelse($results as $res)
        @php
            $link = $res->url ?: ($res->file ? asset('storage/' . $res->file) : null);
            $payload = ['id' => $res->id, 'title' => $res->title, 'result_type' => $res->result_type, 'url' => $res->url, 'source_url' => $res->source_url,
                'published_at' => $res->published_at?->setTimezone($cycle->timezone)->format('Y-m-d\TH:i'), 'has_file' => (bool) $res->file];
        @endphp
        <div class="d-flex justify-content-between align-items-center border-top py-2">
            <div>
                <span class="badge bg-light text-dark border">{{ \App\Models\Counselling\AllotmentResult::TYPES[$res->result_type] }}</span>
                <span class="fw-semibold ms-1">{{ $res->title }}</span>
                <span class="small text-muted ms-1">{{ $res->published_at?->setTimezone($cycle->timezone)->format('d M Y, h:i A') }}</span>
                @if($link)<a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="small ms-2"><i class="fas fa-external-link-alt"></i> Open</a>@endif
            </div>
            @can('cns-counsellings-edit')
            <div class="text-nowrap">
                <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openResult(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                <form action="{{ route('admin.counselling.results.destroy', $res) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this result?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
            </div>
            @endcan
        </div>
    @empty
        <p class="text-muted small mb-0">No result published yet.</p>
    @endforelse
</div></div>

@can('cns-counsellings-edit')
<div class="modal fade" id="resultModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="resultForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.counselling.results.store', $round) }}">
        @csrf <span id="rMethod"></span>
        <input type="hidden" name="_dialog" value="result"><input type="hidden" name="_dialog_id" id="r_dialog_id">
        <div class="modal-header"><h5 class="modal-title">Allotment result</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="alert alert-danger small d-none dialog-errors"></div><div class="row g-3">
            <div class="col-12"><label class="form-label fw-bold">Title *</label><input type="text" name="title" id="r_title" class="form-control" placeholder="Round 1 provisional result" required></div>
            <div class="col-md-6"><label class="form-label fw-bold">Type *</label><select name="result_type" id="r_result_type" class="form-select custom-select2">@foreach(\App\Models\Counselling\AllotmentResult::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Published at * <span class="text-muted small">({{ $cycle->timezone }})</span></label><input type="datetime-local" name="published_at" id="r_published_at" class="form-control" required></div>
            <div class="col-12"><label class="form-label fw-bold">Result file</label><input type="file" name="file" class="form-control" accept=".pdf,.xls,.xlsx,.csv,.zip"><div class="small text-muted" id="r_file_note"></div></div>
            <div class="col-12"><label class="form-label fw-bold">or result URL</label><input type="url" name="url" id="r_url" class="form-control" placeholder="https://"><div class="form-text">A file or a URL is required.</div></div>
            <div class="col-12"><label class="form-label fw-bold">Source URL</label><input type="url" name="source_url" id="r_source_url" class="form-control" placeholder="Official notice"></div>
            @if($choiceOpen)
            <div class="col-12" id="r_choice_warning">
                <div class="alert alert-warning mb-2 small"><i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Choice filling is still open</strong> for this round ({{ $choiceOpen->dateText() }}). Publishing the result now marks it declared while students can still change their choices.</div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="confirm_choice_open" value="1" id="r_confirm_choice_open">
                    <label class="form-check-label" for="r_confirm_choice_open">Publish anyway</label></div>
            </div>
            @endif
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@push('scripts')
<script>
    const rStore = @json(route('admin.counselling.results.store', $round));
    const rBase = @json(url('admin/counselling/results'));
    function openResult(row) {
        const form = document.getElementById('resultForm');
        form.reset();
        const editing = !!(row && row.id);
        form.action = editing ? rBase + '/' + row.id : rStore;
        $('#rMethod').html(editing ? '<input type="hidden" name="_method" value="PUT">' : '');
        $('#r_dialog_id').val(editing ? row.id : '');
        $(form).find('.dialog-errors').addClass('d-none').empty();
        $('#r_title').val(row ? row.title : '');
        $('#r_result_type').val(row ? row.result_type : 'provisional').trigger('change');
        $('#r_published_at').val(row ? row.published_at : '');
        $('#r_url').val(row ? (row.url ?? '') : '');
        $('#r_source_url').val(row ? (row.source_url ?? '') : '');
        $('#r_file_note').text(row && row.has_file ? 'A file is attached. Upload to replace it.' : '');
        // The open-choice-filling warning is about publishing a new result, not editing one.
        $('#r_choice_warning').toggle(!editing);
        $('#r_confirm_choice_open').prop('checked', !!(row && row.confirm_choice_open));
        new bootstrap.Modal(document.getElementById('resultModal')).show();
    }
    @if($errors->any() && old('_dialog') === 'result')
    $(function () {
        openResult(Object.assign({!! json_encode(collect(old())->only(['title', 'result_type', 'published_at', 'url', 'source_url', 'confirm_choice_open']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, {id: @json(old('_dialog_id'))}));
        $('#resultForm .dialog-errors').removeClass('d-none').html(@json($errors->all()).map(e => $('<div>').text(e).html()).join('<br>') + '<br><em>Attach the result file again if you had chosen one.</em>');
    });
    @endif
</script>
@endpush
@endcan
