{{-- Cycle content: eligibility, pattern, fees, syllabus, centres, documents, papers, FAQs + one tab per Schema Builder field. --}}
@php
    use App\Support\ExamModule\ContentBlocks;
    use App\Models\ExamModule\ContentRow;

    $blocks = ContentBlocks::all();
    // The tab of a dialog or extra tab that failed validation stays open; otherwise the last tab used.
    $failedBlock = str_starts_with((string) old('_dialog'), 'block:') ? substr(old('_dialog'), 6) : null;
    $failedCustom = str_starts_with((string) old('_dialog'), 'cf-') ? old('_dialog') : null;
    $activeTab = ($failedBlock && isset($blocks[$failedBlock])) ? $failedBlock : ($failedCustom ?: session('content_tab', 'eligibility'));
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
        @can('exm-schema-manage')<a href="{{ route('admin.exam-module.fields.index', [$cycle->exam, 'cycle' => $cycle->id]) }}" class="btn btn-sm btn-light border"><i class="fas fa-sliders-h me-1"></i> Schema Builder</a>@endcan
    </div>

    <ul class="nav nav-tabs mb-3 flex-wrap" role="tablist">
        @foreach($blocks as $key => $def)
            <li class="nav-item"><button class="nav-link {{ $activeTab === $key ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" type="button">
                {{ $def['title'] }} <span class="badge bg-light text-dark border">{{ $rowsByBlock[$key]->count() }}</span></button></li>
        @endforeach
        @foreach($customFields as $cf)
            @php $filled = filled(is_string($v = $customValues[$cf->id . ':' . ($cf->scope === 'cycle' ? 'c' : 'e')] ?? null) ? trim(strip_tags($v)) : $v); @endphp
            <li class="nav-item"><button class="nav-link {{ $activeTab === 'cf-' . $cf->id ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pane-cf-{{ $cf->id }}" type="button">
                {{ $cf->label }} @if($filled)<i class="fas fa-check-circle text-success small" title="Filled"></i>@endif</button></li>
        @endforeach
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
                        <form action="{{ route('admin.exam-module.content.destroy', [$key, $row->id]) }}" method="POST" class="d-inline" data-confirm="Delete this {{ strtolower($def['label']) }}?">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">Nothing added yet.</p>
            @endforelse
        </div>
        @endforeach

        {{-- Schema Builder tabs: each field is its own tab, saved on its own --}}
        @foreach($customFields as $cf)
        <div class="tab-pane fade {{ $activeTab === 'cf-' . $cf->id ? 'show active' : '' }}" id="pane-cf-{{ $cf->id }}">
            <form action="{{ route('admin.exam-module.custom-values.save', $cycle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="fields[]" value="{{ $cf->id }}"><input type="hidden" name="_dialog" value="cf-{{ $cf->id }}">
                @if($errors->any() && $failedCustom === 'cf-' . $cf->id)
                    <div class="alert alert-danger small">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>
                @endif
                @php
                    $val = $customValues[$cf->id . ':' . ($cf->scope === 'cycle' ? 'c' : 'e')] ?? null;
                    if ($failedCustom === 'cf-' . $cf->id && !in_array($cf->type, ['file', 'group', 'multi_select'])) $val = old("cf.{$cf->id}", $val);
                    $name = "cf[{$cf->id}]";
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border fw-normal">{{ $cf->scope === 'cycle' ? 'This cycle only (copied to the next cycle)' : 'Whole exam (shared by every cycle)' }}</span>
                    @can('exm-schema-manage')<a href="{{ route('admin.exam-module.fields.index', [$cycle->exam, 'cycle' => $cycle->id]) }}" class="small">Rename or delete this tab</a>@endcan
                </div>
                <div class="row g-3">
                    <div class="col-md-{{ in_array($cf->type, ['rich_text', 'group']) ? 12 : 6 }}">
                        @switch($cf->type)
                            @case('rich_text')<textarea name="{{ $name }}" rows="10" class="form-control cf-editor">{{ $val }}</textarea>@break
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
                </div>
                <button class="btn btn-primary mt-3">Save {{ $cf->label }}</button>
            </form>
        </div>
        @endforeach
    </div>
    @if($customFields->isEmpty())
        <p class="text-muted small mb-0 mt-3">Need a tab unique to this exam (e.g. Medical requirements)?
            @can('exm-schema-manage')<a href="{{ route('admin.exam-module.fields.index', [$cycle->exam, 'cycle' => $cycle->id]) }}">Add it in the Schema Builder</a>.@endcan
            Never use it for eligibility, fees or dates.</p>
    @endif
</div></div>

{{-- One modal per block: fields grouped into titled sections (ContentBlocks 'section') --}}
@foreach($blocks as $key => $def)
@php
    $groups = [];
    foreach ($def['fields'] as $f) {
        if (!$groups || !empty($f['section'])) $groups[] = ['title' => $f['section'] ?? null, 'fields' => []];
        $groups[array_key_last($groups)]['fields'][] = $f;
    }
@endphp
<div class="modal fade blk-modal" id="modal-{{ $key }}" tabindex="-1"><div class="modal-dialog modal-{{ $def['size'] ?? 'lg' }} modal-dialog-scrollable"><div class="modal-content">
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.exam-module.content.store', [$cycle, $key]) }}" class="d-flex flex-column overflow-hidden" style="min-height:0"
          data-store="{{ route('admin.exam-module.content.store', [$cycle, $key]) }}" data-update="{{ url('admin/exam-module/content/' . $key) }}">
        @csrf <span class="method-slot"></span>
        <input type="hidden" name="_dialog" value="block:{{ $key }}"><input type="hidden" name="_dialog_id" class="dialog-id">
        <div class="modal-header">
            <div>
                <h5 class="modal-title mb-0"><span class="blk-mode">Add</span> {{ strtolower($def['label']) }}</h5>
                <div class="small text-muted">{{ $cycle->exam->name }} · {{ $cycle->title }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body bg-light">
            <div class="alert alert-danger small d-none dialog-errors"></div>
            @if($cycle->usesStages())
            <div class="border rounded-3 bg-white p-3 mb-3"><div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-bold">Applies to stage</label>
                    <select name="stage_id" class="form-select"><option value="">Whole cycle (every stage)</option>@foreach($cycle->stages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            </div></div>
            @endif
            @foreach($groups as $g)
            <div class="border rounded-3 bg-white p-3 mb-3">
                @if($g['title'])<h6 class="fw-bold text-primary mb-3">{{ $g['title'] }}</h6>@endif
                <div class="row g-3">
            @foreach($g['fields'] as $f)
                @php $opts = in_array($f['type'], ['select', 'multiselect']) ? ContentBlocks::options($f) : []; @endphp
                <div class="col-md-{{ $f['col'] ?? 12 }}">
                    @if($f['type'] === 'bool')
                        <div class="form-check form-switch mt-md-4 pt-md-2">
                            <input type="hidden" name="{{ $f['name'] }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $f['name'] }}" value="1" id="blk-{{ $key }}-{{ $f['name'] }}" data-default="{{ !empty($f['default']) ? 1 : 0 }}">
                            <label class="form-check-label fw-semibold" for="blk-{{ $key }}-{{ $f['name'] }}">{{ $f['label'] }}</label>
                        </div>
                    @else
                        @unless($f['type'] === 'rows' && $f['label'] === $g['title'])
                        <label class="form-label fw-semibold">{{ $f['label'] }}@if(!empty($f['required'])) <span class="text-danger">*</span>@endif</label>
                        @endunless
                        @switch($f['type'])
                            @case('textarea')<textarea name="{{ $f['name'] }}" rows="3" class="form-control"></textarea>@break
                            @case('richtext')<textarea name="{{ $f['name'] }}" id="blk-{{ $key }}-{{ $f['name'] }}" rows="8" class="form-control blk-editor"></textarea>@break
                            @case('list')<textarea name="{{ $f['name'] }}" rows="4" class="form-control" placeholder="One per line"></textarea>@break
                            @case('multiselect')
                                <select name="{{ $f['name'] }}[]" class="form-select blk-multi" multiple data-placeholder="Everyone">
                                @foreach($opts as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>@break
                            @case('number')<input type="number" step="any" name="{{ $f['name'] }}" class="form-control" @isset($f['min']) min="{{ $f['min'] }}" @endisset @isset($f['max']) max="{{ $f['max'] }}" @endisset data-default="{{ $f['default'] ?? '' }}">@break
                            @case('date')<input type="date" name="{{ $f['name'] }}" class="form-control">@break
                            @case('file')<input type="file" name="{{ $f['name'] }}" class="form-control" accept=".{{ str_replace(',', ',.', $f['mimes'] ?? 'pdf') }}"><div class="small text-muted cur-file" data-file="{{ $f['name'] }}"></div>@break
                            @case('select')
                                <select name="{{ $f['name'] }}" class="form-select" data-default="{{ $f['default'] ?? '' }}"><option value="">Select</option>
                                @foreach($opts as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>
                                @if(($f['quick_add'] ?? null) === 'city')
                                    @unless(count($opts))<div class="form-text text-warning">The City master is empty.@cannot('exm-cities-manage') Ask an admin to add cities.@endcannot</div>@endunless
                                    @can('exm-cities-manage')
                                    <a href="#" class="small" onclick="this.nextElementSibling.classList.toggle('d-none'); return false;">+ Add a new city</a>
                                    <div class="d-none border rounded p-2 mt-1 quick-city" data-url="{{ route('admin.exam-module.cities.store') }}">
                                        <input type="text" class="form-control form-control-sm mb-1 qc-name" placeholder="City name">
                                        <select class="form-select form-select-sm mb-1 qc-state"><option value="">State (optional)</option>@foreach(\App\Models\ExamModule\Exam::STATES as $st)<option>{{ $st }}</option>@endforeach</select>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="quickAddCity(this.closest('.quick-city'))">Add city</button>
                                        <span class="small text-danger qc-error ms-2"></span>
                                    </div>
                                    @endcan
                                @endif
                                @break
                            @case('rows')
                                <div class="table-responsive border rounded-2"><table class="table table-sm align-middle mb-0">
                                    <thead class="table-light"><tr>@foreach($f['sub'] as $s)<th class="small fw-semibold">{{ $s['label'] }}</th>@endforeach<th style="width:40px"></th></tr></thead>
                                    <tbody class="rows-box" data-name="{{ $f['name'] }}" data-sub="{{ json_encode(array_map(fn ($s) => $s['type'] === 'select' ? ['options' => ContentBlocks::options($s)] + $s : $s, $f['sub'])) }}"></tbody>
                                </table></div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addRow(this.closest('form').querySelector('.rows-box[data-name={{ $f['name'] }}]'))"><i class="fas fa-plus me-1"></i> {{ $f['add'] ?? 'Add row' }}</button>@break
                            @default<input type="text" name="{{ $f['name'] }}" class="form-control" data-default="{{ $f['default'] ?? '' }}">
                        @endswitch
                        @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
                    @endif
                </div>
            @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="fas fa-check me-1"></i> Save {{ strtolower($def['label']) }}</button></div>
    </form>
</div></div></div>
@endforeach

@push('scripts')
<script>
    // ---- repeating rows (age relaxation, sections) ----
    const esc = v => String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    function subInput(s, name, val) {
        const base = `name="${name}" class="form-control form-control-sm"`;
        if (s.type === 'select') {
            const opts = Object.entries(s.options).map(([v, l]) => `<option value="${esc(v)}" ${String(val) === String(v) ? 'selected' : ''}>${esc(l)}</option>`).join('');
            return `<select ${base.replace('form-control', 'form-select')}><option value="">Select</option>${opts}</select>`;
        }
        const range = (s.min !== undefined ? ` min="${s.min}"` : '') + (s.max !== undefined ? ` max="${s.max}"` : '');
        return `<input type="${s.type === 'number' ? 'number' : 'text'}" step="any"${range} ${base} value="${esc(val)}">`;
    }
    // Rows are table rows: one input per column, numbered by a counter so removing a row never reuses an index.
    function addRow(box, data) {
        const sub = JSON.parse(box.dataset.sub), i = box.dataset.next = (+box.dataset.next || box.children.length) + 1;
        const row = document.createElement('tr');
        row.innerHTML = sub.map(s => `<td>${subInput(s, `${box.dataset.name}[${i}][${s.name}]`, data ? data[s.name] : '')}</td>`).join('')
            + '<td class="text-end"><button type="button" class="btn btn-sm btn-light border text-danger" title="Remove" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        box.appendChild(row);
    }
    // ---- dialog editors (same TinyMCE as the Schema Builder tabs) and multi-selects, set up when a dialog first opens ----
    document.addEventListener('focusin', e => { if (e.target.closest('.tox-tinymce, .tox-tinymce-aux, .tox-dialog')) e.stopImmediatePropagation(); }, true);
    function setupDialog(modalEl) {
        if (modalEl.dataset.ready) return;
        modalEl.dataset.ready = '1';
        $(modalEl).find('.blk-multi').each(function () { $(this).select2({ width: '100%', dropdownParent: $(modalEl), placeholder: this.dataset.placeholder, allowClear: true }); });
        if (modalEl.querySelector('.blk-editor') && typeof initializeTinyMCE === 'function') initializeTinyMCE('#' + modalEl.id + ' .blk-editor', 260);
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
    // ---- Schema Builder rich-text tabs use the CRM's standard editor ----
    if (document.querySelector('.cf-editor') && typeof initializeTinyMCE === 'function') initializeTinyMCE('.cf-editor', 400);

    // ---- add a city from the centre form without leaving the page ----
    async function quickAddCity(box) {
        const form = box.closest('form'), select = form.querySelector('select[name="city_id"]');
        const name = box.querySelector('.qc-name').value.trim(), state = box.querySelector('.qc-state').value;
        const err = box.querySelector('.qc-error'); err.textContent = '';
        if (!name) { err.textContent = 'Enter a city name.'; return; }
        const res = await fetch(box.dataset.url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value },
            body: JSON.stringify({ name, state: state || null }),
        });
        const body = await res.json().catch(() => ({}));
        if (!res.ok) { err.textContent = (body.errors && Object.values(body.errors)[0][0]) || body.message || 'Could not add the city.'; return; }
        select.add(new Option(body.label, body.id, true, true));
        $(select).trigger('change');
        box.querySelector('.qc-name').value = '';
        box.classList.add('d-none');
    }

    // ---- block modal ----
    function openBlock(key, row) {
        const modalEl = document.getElementById('modal-' + key), form = modalEl.querySelector('form');
        form.reset();
        form.querySelectorAll('.rows-box').forEach(b => { b.innerHTML = ''; b.dataset.next = 0; });
        form.querySelectorAll('.cur-file').forEach(e => e.textContent = '');
        form.querySelectorAll('[data-default]').forEach(el => {
            if (el.type === 'checkbox') el.checked = el.dataset.default === '1';
            else if (el.dataset.default !== '') el.value = el.dataset.default;
        });
        const editing = !!(row && row.id);
        modalEl.querySelector('.blk-mode').textContent = editing ? 'Edit' : 'Add';
        form.action = editing ? form.dataset.update + '/' + row.id : form.dataset.store;
        form.querySelector('.method-slot').innerHTML = editing ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.querySelector('.dialog-id').value = editing ? row.id : '';
        $(form).find('.dialog-errors').addClass('d-none').empty();
        if (row) {
            Object.entries(row).forEach(([name, val]) => {
                if (name === '_files' || val === null) return;
                const multi = form.querySelector(`select[multiple][name="${name}[]"]`);
                if (multi) { const vals = [].concat(val).map(String); [...multi.options].forEach(o => o.selected = vals.includes(o.value)); return; }
                // Rows come back as a list, or (after a failed save) keyed by their row number.
                if (Array.isArray(val) || typeof val === 'object') { const box = form.querySelector(`.rows-box[data-name="${name}"]`); if (box) Object.values(val).forEach(r => addRow(box, r)); return; }
                const el = form.querySelector(`[name="${name}"]:not([type=hidden])`);
                if (!el) return;
                if (el.type === 'checkbox') el.checked = [true, 1, '1', 'true', 'on'].includes(val); else el.value = val;
            });
            Object.entries(row._files || {}).forEach(([n, f]) => { const e = form.querySelector(`.cur-file[data-file="${n}"]`); if (e && f) e.textContent = 'Current file: ' + f + ' (upload to replace)'; });
        }
        form.querySelectorAll('.rows-box').forEach(b => { if (!b.children.length) addRow(b); });
        setupDialog(modalEl);
        $(form).find('.blk-multi').trigger('change');
        // An editor that already exists keeps its old content after reset(): load this row's text into it.
        form.querySelectorAll('.blk-editor').forEach(t => { const ed = window.tinymce && tinymce.get(t.id); if (ed && ed.initialized) ed.setContent(t.value || ''); });
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    // A validation error reopens the dialog that was submitted (e.g. the fee), not the event dialog, with what was typed.
    @if($errors->any() && $failedBlock && isset($blocks[$failedBlock]))
    $(function () {
        openBlock(@json($failedBlock), Object.assign({!! json_encode(collect(old())->except(['_token', '_method', '_dialog', '_dialog_id']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, {id: @json(old('_dialog_id'))}));
        const box = document.querySelector('#modal-' + @json($failedBlock) + ' .dialog-errors');
        const files = document.querySelector('#modal-' + @json($failedBlock) + ' input[type=file]') ? '<br><em>Attach any file again: browsers do not keep a chosen file after a failed save.</em>' : '';
        box.classList.remove('d-none');
        box.innerHTML = @json(array_values(array_unique($errors->all()))).map(e => $('<div>').text(e).html()).join('<br>') + files;
    });
    @endif
</script>
@endpush
