{{-- Eligibility / process / fees / documents / rules for a cycle (round = null) or one round.
     Needs: $cycle, $round (nullable). Round rows are shown merged with the cycle's: inherited, overridden or round-only. --}}
@php
    use App\Models\Counselling\CnsRow;
    use App\Support\Counselling\CnsBlocks;
    use App\Support\Counselling\Inheritance;

    $isRound = (bool) $round;
    $scope = $isRound ? 'round' : 'cycle';
    $allBlocks = collect(CnsBlocks::all())->filter(fn ($d) => in_array($scope, $d['scopes']));
    // The tab of a dialog that failed validation stays open; otherwise the last tab used.
    $failedBlock = str_starts_with((string) old('_dialog'), 'cns:') ? substr(old('_dialog'), 4) : null;
    $activeBlock = ($failedBlock && $allBlocks->has($failedBlock)) ? $failedBlock : session('block_tab', $allBlocks->keys()->first());
    $defs = [];
    $lists = [];
    foreach ($allBlocks as $key => $d) {
        $defs[$key] = CnsBlocks::resolved($key, $cycle->counselling_id);
        $lists[$key] = $isRound
            ? Inheritance::effective($key, $round)
            : CnsRow::forScope($key, $cycle->id)->map(fn ($r) => ['row' => $r, 'source' => 'cycle', 'replaces' => null]);
    }
    $sourceBadge = [
        'inherited' => ['bg-light text-dark border', 'From cycle'],
        'overridden' => ['bg-warning-subtle text-warning', 'Overridden in this round'],
        'round' => ['bg-primary-subtle text-primary', 'This round only'],
        'cycle' => ['', ''],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    <h6 class="fw-bold mb-3">{{ $isRound ? 'Rules, fees & documents for this round' : 'Eligibility, process, fees, documents & rules' }}
        <span class="text-muted fw-normal small">{{ $isRound ? '(a round stores only what differs from the cycle)' : '(copied forward with Copy Cycle)' }}</span></h6>

    <ul class="nav nav-tabs mb-3 flex-wrap">
        @foreach($allBlocks as $key => $d)
            <li class="nav-item"><button class="nav-link {{ $activeBlock === $key ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#cns-{{ $key }}" type="button">
                {{ $d['title'] }} <span class="badge bg-light text-dark border">{{ $lists[$key]->count() }}</span></button></li>
        @endforeach
    </ul>

    <div class="tab-content">
    @foreach($allBlocks as $key => $d)
        @php $def = $defs[$key]; @endphp
        <div class="tab-pane fade {{ $activeBlock === $key ? 'show active' : '' }}" id="cns-{{ $key }}">
            <div class="d-flex justify-content-between align-items-start mb-2 gap-3">
                <p class="small text-muted mb-0">{{ $d['help'] ?? '' }}</p>
                @can('cns-counsellings-edit')
                <button class="btn btn-sm btn-primary text-nowrap" onclick="openCns('{{ $key }}')"><i class="fas fa-plus me-1"></i> Add {{ strtolower($d['label']) }}</button>
                @endcan
            </div>

            @forelse($lists[$key] as $item)
                @php
                    $row = $item['row'];
                    $payload = $row->only(array_merge(['id'], array_column(array_filter($def['fields'], fn ($f) => $f['type'] !== 'children'), 'name')));
                    $files = [];
                    foreach ($def['fields'] as $f) {
                        if ($f['type'] === 'list') $payload[$f['name']] = implode("\n", $row->{$f['name']} ?? []);
                        if ($f['type'] === 'date') $payload[$f['name']] = $row->{$f['name']}?->format('Y-m-d');
                        if ($f['type'] === 'file') { $files[$f['name']] = $row->{$f['name']} ? basename($row->{$f['name']}) : null; $payload[$f['name']] = null; }
                        if ($f['type'] === 'children') {
                            $payload[$f['name']] = \Illuminate\Support\Facades\DB::table($f['table'])->where($f['fk'], $row->id)
                                ->get(array_column($f['sub'], 'name'))->map(fn ($c) => (array) $c)->all();
                        }
                    }
                    $payload['_files'] = $files;
                    [$cls, $label] = $sourceBadge[$item['source']];
                    $step = ($key === 'process' && $row->event_type_id)
                        ? \App\Models\ExamModule\CycleEvent::where('counselling_cycle_id', $cycle->id)->where('event_type_id', $row->event_type_id)
                            ->where(fn ($q) => $q->where('round_id', $round?->id)->orWhereNull('round_id'))->orderByRaw('round_id is null')->first()
                        : null;
                @endphp
                <div class="d-flex justify-content-between align-items-start border-top py-2 gap-2">
                    <div>
                        @if($label)<span class="badge {{ $cls }} me-1">{{ $label }}</span>@endif
                        {{ implode(' · ', array_map('strval', ($d['summary'])($row))) ?: '-' }}
                        @if($step)<span class="small text-muted ms-1">({{ $step->dateText() }})</span>@endif
                        @foreach(CnsBlocks::fileFields($def) as $ff)
                            @if($row->$ff)<a class="small ms-2" href="{{ asset('storage/' . $row->$ff) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip"></i> {{ basename($row->$ff) }}</a>@endif
                        @endforeach
                        @if($item['source'] === 'overridden')
                            <div class="small text-muted ms-1">Cycle value: {{ implode(' · ', array_map('strval', ($d['summary'])($item['replaces']))) }}</div>
                        @endif
                    </div>
                    @can('cns-counsellings-edit')
                    <div class="text-nowrap">
                        @if($item['source'] === 'inherited' && $isRound)
                            @if(($d['inherit'] ?? 'add') === 'replace')
                            <form action="{{ route('admin.counselling.blocks.override', [$round, $key, $row->id]) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-warning">Override</button></form>
                            @endif
                        @else
                            <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openCns('{{ $key }}', JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                            <form action="{{ route('admin.counselling.blocks.destroy', [$key, $row->id]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ $item['source'] === 'overridden' ? 'Reset this round to the cycle value?' : 'Delete this ' . strtolower($d['label']) . '?' }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border text-danger" title="{{ $item['source'] === 'overridden' ? 'Reset to cycle value' : 'Delete' }}">
                                    <i class="fas {{ $item['source'] === 'overridden' ? 'fa-undo' : 'fa-trash-alt' }}"></i></button>
                            </form>
                        @endif
                    </div>
                    @endcan
                </div>
            @empty
                <p class="text-muted small mb-0">Nothing added yet.</p>
            @endforelse
        </div>
    @endforeach
    </div>
</div></div>

{{-- One modal per block --}}
@foreach($allBlocks as $key => $d)
@php $def = $defs[$key]; @endphp
<div class="modal fade" id="cnsModal-{{ $key }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" enctype="multipart/form-data"
          data-store="{{ $isRound ? route('admin.counselling.blocks.store-round', [$round, $key]) : route('admin.counselling.blocks.store-cycle', [$cycle, $key]) }}"
          data-update="{{ url('admin/counselling/blocks/' . $key) }}"
          action="{{ $isRound ? route('admin.counselling.blocks.store-round', [$round, $key]) : route('admin.counselling.blocks.store-cycle', [$cycle, $key]) }}">
        @csrf <span class="method-slot"></span>
        <input type="hidden" name="_dialog" value="cns:{{ $key }}"><input type="hidden" name="_dialog_id" class="dialog-id">
        <div class="modal-header"><h5 class="modal-title">{{ $d['title'] }}@if($isRound) <span class="text-muted fs-6">· {{ $round->name }}</span>@endif</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="alert alert-danger small d-none dialog-errors"></div><div class="row g-3">
            @foreach($def['fields'] as $f)
                <div class="col-md-{{ $f['col'] ?? 12 }}">
                    @if($f['type'] === 'bool')
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="{{ $f['name'] }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $f['name'] }}" value="1" data-default="{{ !empty($f['default']) ? 1 : 0 }}">
                            <label class="form-check-label">{{ $f['label'] }}</label></div>
                    @else
                        <label class="form-label fw-bold">{{ $f['label'] }}@if(!empty($f['required'])) <span class="text-danger">*</span>@endif</label>
                        @switch($f['type'])
                            @case('textarea')<textarea name="{{ $f['name'] }}" rows="3" class="form-control"></textarea>@break
                            @case('list')<textarea name="{{ $f['name'] }}" rows="3" class="form-control"></textarea>@break
                            @case('number')<input type="number" step="any" name="{{ $f['name'] }}" class="form-control" @isset($f['min']) min="{{ $f['min'] }}" @endisset @isset($f['max']) max="{{ $f['max'] }}" @endisset data-default="{{ $f['default'] ?? '' }}">@break
                            @case('date')<input type="date" name="{{ $f['name'] }}" class="form-control">@break
                            @case('url')<input type="url" name="{{ $f['name'] }}" class="form-control" placeholder="https://">@break
                            @case('file')<input type="file" name="{{ $f['name'] }}" class="form-control" accept=".{{ str_replace(',', ',.', $f['mimes'] ?? 'pdf') }}"><div class="small text-muted cns-cur-file" data-file="{{ $f['name'] }}"></div>@break
                            @case('select')
                                <select name="{{ $f['name'] }}" class="form-select" data-default="{{ $f['default'] ?? '' }}">
                                    <option value="">{{ $f['blank'] ?? 'Select' }}</option>
                                    @foreach($f['options'] as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>@break
                            @case('multiselect')
                                <select name="{{ $f['name'] }}[]" class="form-select" multiple>@foreach($f['options'] as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>@break
                            @case('children')
                                <div class="cns-rows" data-name="{{ $f['name'] }}" data-sub="{{ json_encode($f['sub']) }}"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addCnsRow(this.previousElementSibling)">Add row</button>@break
                            @default<input type="text" name="{{ $f['name'] }}" class="form-control" data-default="{{ $f['default'] ?? '' }}">
                        @endswitch
                        @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
                    @endif
                </div>
            @endforeach
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endforeach

@push('scripts')
<script>
    // ---- repeating child rows (category-wise minimum scores) ----
    function cnsSubInput(s, name, val) {
        const base = `name="${name}" class="form-control form-control-sm" placeholder="${s.label}" title="${s.label}"`;
        if (s.type === 'select') {
            const opts = Object.entries(s.options || {}).map(([v, l]) => `<option value="${v}" ${String(val) === String(v) ? 'selected' : ''}>${l}</option>`).join('');
            return `<select ${base}><option value="">${s.blank || s.label}</option>${opts}</select>`;
        }
        return `<input type="number" step="any" ${base} value="${val ?? ''}">`;
    }
    function addCnsRow(box, data) {
        const sub = JSON.parse(box.dataset.sub), i = box.children.length;
        const row = document.createElement('div');
        row.className = 'd-flex gap-1 mb-1';
        row.innerHTML = sub.map(s => `<div class="flex-fill">${cnsSubInput(s, `${box.dataset.name}[${i}][${s.name}]`, data ? data[s.name] : '')}</div>`).join('')
            + '<button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.parentElement.remove()">&times;</button>';
        box.appendChild(row);
    }
    // ---- block modal ----
    function openCns(key, row) {
        const modalEl = document.getElementById('cnsModal-' + key), form = modalEl.querySelector('form');
        form.reset();
        form.querySelectorAll('.cns-rows').forEach(b => b.innerHTML = '');
        form.querySelectorAll('.cns-cur-file').forEach(e => e.textContent = '');
        form.querySelectorAll('[data-default]').forEach(el => {
            if (el.type === 'checkbox') el.checked = el.dataset.default === '1';
            else if (el.dataset.default !== '') el.value = el.dataset.default;
        });
        const editing = !!(row && row.id);
        form.action = editing ? form.dataset.update + '/' + row.id : form.dataset.store;
        form.querySelector('.method-slot').innerHTML = editing ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.querySelector('.dialog-id').value = editing ? row.id : '';
        $(form).find('.dialog-errors').addClass('d-none').empty();
        if (row) {
            Object.entries(row).forEach(([name, val]) => {
                if (name === '_files' || name === 'id' || val === null) return;
                const box = form.querySelector(`.cns-rows[data-name="${name}"]`);
                if (box) { (val || []).forEach(r => addCnsRow(box, r)); return; }
                const multi = form.querySelector(`select[name="${name}[]"]`);
                if (multi) { $(multi).val(val || []).trigger('change'); return; }
                const el = form.querySelector(`[name="${name}"]:not([type=hidden])`);
                if (!el) return;
                if (el.type === 'checkbox') el.checked = [true, 1, '1', 'true', 'on'].includes(val); else { el.value = val; $(el).trigger('change'); }
            });
            Object.entries(row._files || {}).forEach(([n, f]) => { const e = form.querySelector(`.cns-cur-file[data-file="${n}"]`); if (e && f) e.textContent = 'Current file: ' + f + ' (upload to replace)'; });
        }
        form.querySelectorAll('.cns-rows').forEach(b => { if (!b.children.length) addCnsRow(b); });
        new bootstrap.Modal(modalEl).show();
    }
    // A validation error reopens the dialog that was submitted (e.g. the fee or rule), not another one, with what was typed.
    @if($errors->any() && $failedBlock && $allBlocks->has($failedBlock))
    $(function () {
        const typed = Object.assign({!! json_encode(collect(old())->except(['_token', '_method', '_dialog', '_dialog_id']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, {id: @json(old('_dialog_id'))});
        openCns(@json($failedBlock), typed);
        const box = document.querySelector('#cnsModal-' + @json($failedBlock) + ' .dialog-errors');
        const files = document.querySelector('#cnsModal-' + @json($failedBlock) + ' input[type=file]') ? '<br><em>Attach any file again: browsers do not keep a chosen file after a failed save.</em>' : '';
        box.classList.remove('d-none');
        box.innerHTML = @json($errors->all()).map(e => $('<div>').text(e).html()).join('<br>') + files;
    });
    @endif
</script>
@endpush
