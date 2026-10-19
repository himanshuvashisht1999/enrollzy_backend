{{-- Cycle content: eligibility, pattern, fees, syllabus, centres, documents, papers, FAQs + Schema Builder extras. --}}
@php
    use App\Support\ExamModule\ContentBlocks;
    use App\Models\ExamModule\ContentRow;

    $blocks = ContentBlocks::all();
    $activeTab = session('content_tab', 'eligibility');
    $customFields = $cycle->exam->customFields()->get();
    $customValues = \App\Models\ExamModule\CustomValue::whereIn('field_id', $customFields->pluck('id'))
        ->where(fn ($q) => $q->whereNull('cycle_id')->orWhere('cycle_id', $cycle->id))->get()
        ->mapWithKeys(fn ($v) => [$v->field_id . ':' . ($v->cycle_id ? 'c' : 'e') => $v->value]);
    $rowsByBlock = [];
    foreach ($blocks as $key => $def) {
        $rowsByBlock[$key] = ContentRow::block($key)->newQuery()
            ->with($key === 'centres' ? ['stage', 'city'] : ['stage'])
            ->where('cycle_id', $cycle->id)->orderBy('id')->get();
    }
@endphp

<div class="card border-0 shadow-sm rounded-4 mt-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h6 class="fw-bold mb-0">Cycle details <span class="text-muted fw-normal small">(copied forward with Copy Cycle)</span></h6>
        @can('exm-schema-manage')<a href="{{ route('admin.exam-module.fields.index', $cycle->exam) }}" class="btn btn-sm btn-light border"><i class="fas fa-sliders-h me-1"></i> Schema Builder</a>@endcan
    </div>

    <ul class="nav nav-tabs mb-3 flex-wrap" role="tablist">
        @foreach($blocks as $key => $def)
            <li class="nav-item"><button class="nav-link {{ $activeTab === $key ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" type="button">
                {{ $def['title'] }} <span class="badge bg-light text-dark border">{{ $rowsByBlock[$key]->count() }}</span></button></li>
        @endforeach
        <li class="nav-item"><button class="nav-link {{ $activeTab === 'custom' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pane-custom" type="button">
            Extra details <span class="badge bg-light text-dark border">{{ $customFields->count() }}</span></button></li>
    </ul>

    <div class="tab-content">
        @foreach($blocks as $key => $def)
        <div class="tab-pane fade {{ $activeTab === $key ? 'show active' : '' }}" id="pane-{{ $key }}">
            <div class="d-flex justify-content-end mb-2">
                <button class="btn btn-sm btn-primary" onclick="openBlock('{{ $key }}')"><i class="fas fa-plus me-1"></i> Add {{ strtolower($def['label']) }}</button>
            </div>
            @forelse($rowsByBlock[$key] as $row)
                @php
                    $payload = $row->only(array_merge(['id', 'stage_id'], array_column($def['fields'], 'name')));
                    $filesInfo = [];
                    foreach ($def['fields'] as $f) {
                        if ($f['type'] === 'list') $payload[$f['name']] = implode("\n", $row->{$f['name']} ?? []);
                        if ($f['type'] === 'date') $payload[$f['name']] = $row->{$f['name']}?->format('Y-m-d');
                        if ($f['type'] === 'file') { $filesInfo[$f['name']] = $row->{$f['name']} ? basename($row->{$f['name']}) : null; $payload[$f['name']] = null; }
                    }
                    $payload['_files'] = $filesInfo;
                @endphp
                <div class="d-flex justify-content-between align-items-start border-top py-2 gap-2">
                    <div>
                        @if($cycle->usesStages() && $row->stage)<span class="badge bg-primary-subtle text-primary me-1">{{ $row->stage->name }}</span>
                        @elseif($cycle->usesStages())<span class="badge bg-light text-dark border me-1">Whole cycle</span>@endif
                        {{ implode(' · ', array_map('strval', ($def['summary'])($row))) ?: '-' }}
                        @foreach(ContentBlocks::fileFields($def) as $ff)
                            @if($row->$ff)<a class="small ms-2" href="{{ asset('storage/' . $row->$ff) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip"></i> {{ basename($row->$ff) }}</a>@endif
                        @endforeach
                    </div>
                    <div class="text-nowrap">
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openBlock('{{ $key }}', JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.exam-module.content.destroy', [$key, $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this {{ strtolower($def['label']) }}?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">Nothing added yet.</p>
            @endforelse
        </div>
        @endforeach

        {{-- Schema Builder values --}}
        <div class="tab-pane fade {{ $activeTab === 'custom' ? 'show active' : '' }}" id="pane-custom">
            @if($customFields->isEmpty())
                <p class="text-muted small mb-0">No extra fields are defined for this exam.
                    @can('exm-schema-manage')<a href="{{ route('admin.exam-module.fields.index', $cycle->exam) }}">Open the Schema Builder</a>@endcan
                    Use it only for details unique to this exam, never for eligibility, fees or dates.</p>
            @else
            <form action="{{ route('admin.exam-module.custom-values.save', $cycle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                @foreach($customFields as $cf)
                    @php
                        $val = $customValues[$cf->id . ':' . ($cf->scope === 'cycle' ? 'c' : 'e')] ?? null;
                        $name = "cf[{$cf->id}]";
                    @endphp
                    <div class="col-md-{{ in_array($cf->type, ['rich_text', 'group']) ? 12 : 6 }}">
                        <label class="form-label fw-bold">{{ $cf->label }}
                            <span class="badge bg-light text-dark border fw-normal">{{ $cf->scope === 'cycle' ? 'This cycle' : 'Whole exam' }}</span></label>
                        @switch($cf->type)
                            @case('rich_text')<textarea name="{{ $name }}" rows="4" class="form-control">{{ $val }}</textarea>@break
                            @case('number')<input type="number" step="any" name="{{ $name }}" class="form-control" value="{{ $val }}">@break
                            @case('date')<input type="date" name="{{ $name }}" class="form-control" value="{{ $val }}">@break
                            @case('url')<input type="url" name="{{ $name }}" class="form-control" value="{{ $val }}" placeholder="https://">@break
                            @case('dropdown')
                                <select name="{{ $name }}" class="form-select custom-select2"><option value="">-</option>
                                @foreach($cf->options ?? [] as $o)<option value="{{ $o }}" @selected($val === $o)>{{ $o }}</option>@endforeach</select>@break
                            @case('multi_select')
                                <select name="{{ $name }}[]" class="form-select" multiple>
                                @foreach($cf->options ?? [] as $o)<option value="{{ $o }}" @selected(in_array($o, (array) $val))>{{ $o }}</option>@endforeach</select>@break
                            @case('file')
                                <input type="file" name="{{ $name }}" class="form-control">
                                @if($val)<a class="small" href="{{ asset('storage/' . $val) }}" target="_blank" rel="noopener">Current: {{ basename($val) }}</a>@endif @break
                            @case('group')
                                @php $keys = \App\Http\Controllers\Admin\ExamModule\CustomFieldController::groupKeys($cf); @endphp
                                <div class="group-rows" data-name="{{ $name }}" data-keys="{{ json_encode($keys) }}" data-labels="{{ json_encode($cf->options) }}" data-rows="{{ json_encode($val ?? []) }}"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addGroupRow(this.previousElementSibling)">Add row</button>@break
                            @default<input type="text" name="{{ $name }}" class="form-control" value="{{ $val }}">
                        @endswitch
                    </div>
                @endforeach
                </div>
                <button class="btn btn-primary mt-3">Save extra details</button>
            </form>
            @endif
        </div>
    </div>
</div></div>

{{-- One modal per block --}}
@foreach($blocks as $key => $def)
<div class="modal fade" id="modal-{{ $key }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.exam-module.content.store', [$cycle, $key]) }}"
          data-store="{{ route('admin.exam-module.content.store', [$cycle, $key]) }}" data-update="{{ url('admin/exam-module/content/' . $key) }}">
        @csrf <span class="method-slot"></span>
        <div class="modal-header"><h5 class="modal-title">{{ $def['title'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            @if($cycle->usesStages())
            <div class="col-md-6"><label class="form-label fw-bold">Stage <span class="text-muted small">(optional)</span></label>
                <select name="stage_id" class="form-select"><option value="">Whole cycle</option>@foreach($cycle->stages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="col-md-6"></div>
            @endif
            @foreach($def['fields'] as $f)
                @php $opts = $f['type'] === 'select' ? ContentBlocks::options($f) : []; @endphp
                <div class="col-md-{{ $f['col'] ?? 12 }}">
                    @if($f['type'] === 'bool')
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="{{ $f['name'] }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $f['name'] }}" value="1" data-default="{{ !empty($f['default']) ? 1 : 0 }}">
                            <label class="form-check-label">{{ $f['label'] }}</label>
                        </div>
                    @else
                        <label class="form-label fw-bold">{{ $f['label'] }}@if(!empty($f['required'])) <span class="text-danger">*</span>@endif</label>
                        @switch($f['type'])
                            @case('textarea')<textarea name="{{ $f['name'] }}" rows="3" class="form-control"></textarea>@break
                            @case('list')<textarea name="{{ $f['name'] }}" rows="4" class="form-control"></textarea>@break
                            @case('number')<input type="number" step="any" name="{{ $f['name'] }}" class="form-control" @isset($f['min']) min="{{ $f['min'] }}" @endisset @isset($f['max']) max="{{ $f['max'] }}" @endisset data-default="{{ $f['default'] ?? '' }}">@break
                            @case('date')<input type="date" name="{{ $f['name'] }}" class="form-control">@break
                            @case('file')<input type="file" name="{{ $f['name'] }}" class="form-control" accept=".{{ str_replace(',', ',.', $f['mimes'] ?? 'pdf') }}"><div class="small text-muted cur-file" data-file="{{ $f['name'] }}"></div>@break
                            @case('select')
                                <select name="{{ $f['name'] }}" class="form-select" data-default="{{ $f['default'] ?? '' }}"><option value="">Select</option>
                                @foreach($opts as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>@break
                            @case('rows')
                                <div class="rows-box" data-name="{{ $f['name'] }}" data-sub="{{ json_encode($f['sub']) }}"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addRow(this.previousElementSibling)">Add row</button>@break
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
    // ---- repeating rows (age relaxation, sections) ----
    function subInput(s, name, val) {
        const base = `name="${name}" class="form-control form-control-sm" placeholder="${s.label}"`;
        if (s.type === 'select') {
            const opts = Object.entries(s.options).map(([v, l]) => `<option value="${v}" ${String(val) === String(v) ? 'selected' : ''}>${l}</option>`).join('');
            return `<select ${base}><option value="">${s.label}</option>${opts}</select>`;
        }
        return `<input type="${s.type === 'number' ? 'number' : 'text'}" step="any" ${base} value="${val ?? ''}">`;
    }
    function addRow(box, data) {
        const sub = JSON.parse(box.dataset.sub), i = box.children.length;
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-1';
        row.innerHTML = sub.map(s => `<div class="flex-fill">${subInput(s, `${box.dataset.name}[${i}][${s.name}]`, data ? data[s.name] : '')}</div>`).join('')
            + '<button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.parentElement.remove()">&times;</button>';
        box.appendChild(row);
    }
    // ---- Schema Builder group rows ----
    function addGroupRow(box, data) {
        const keys = JSON.parse(box.dataset.keys), labels = JSON.parse(box.dataset.labels), i = box.children.length;
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-1';
        row.innerHTML = keys.map((k, n) => `<input class="form-control form-control-sm" placeholder="${labels[n]}" name="${box.dataset.name}[${i}][${k}]" value="${(data && data[k]) ? String(data[k]).replace(/"/g, '&quot;') : ''}">`).join('')
            + '<button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.parentElement.remove()">&times;</button>';
        box.appendChild(row);
    }
    document.querySelectorAll('.group-rows').forEach(b => { const rows = JSON.parse(b.dataset.rows || '[]'); (rows.length ? rows : [null]).forEach(r => addGroupRow(b, r)); });

    // ---- block modal ----
    function openBlock(key, row) {
        const modalEl = document.getElementById('modal-' + key), form = modalEl.querySelector('form');
        form.reset();
        form.querySelectorAll('.rows-box').forEach(b => b.innerHTML = '');
        form.querySelectorAll('.cur-file').forEach(e => e.textContent = '');
        form.querySelectorAll('[data-default]').forEach(el => {
            if (el.type === 'checkbox') el.checked = el.dataset.default === '1';
            else if (el.dataset.default !== '') el.value = el.dataset.default;
        });
        form.action = row ? form.dataset.update + '/' + row.id : form.dataset.store;
        form.querySelector('.method-slot').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) {
            Object.entries(row).forEach(([name, val]) => {
                if (name === '_files' || val === null) return;
                if (Array.isArray(val)) { const box = form.querySelector(`.rows-box[data-name="${name}"]`); if (box) val.forEach(r => addRow(box, r)); return; }
                const el = form.querySelector(`[name="${name}"]:not([type=hidden])`);
                if (!el) return;
                if (el.type === 'checkbox') el.checked = !!val; else el.value = val;
            });
            Object.entries(row._files || {}).forEach(([n, f]) => { const e = form.querySelector(`.cur-file[data-file="${n}"]`); if (e && f) e.textContent = 'Current file: ' + f + ' (upload to replace)'; });
        }
        form.querySelectorAll('.rows-box').forEach(b => { if (!b.children.length) addRow(b); });
        new bootstrap.Modal(modalEl).show();
    }
</script>
@endpush
