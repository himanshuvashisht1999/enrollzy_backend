@extends('admin.layouts.master')

@section('title', $def['title'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">{{ $def['title'] }}</h4>
        <p class="text-muted mb-0">{{ $def['help'] }}</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-light border dropdown-toggle" data-bs-toggle="dropdown">Other masters</button>
            <ul class="dropdown-menu">
                @foreach(\App\Support\Counselling\Masters::all() as $key => $m)
                    <li><a class="dropdown-item {{ $key === $master ? 'active' : '' }}" href="{{ route('admin.counselling.masters.index', $key) }}">{{ $m['title'] }}</a></li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border">Counselling</a>
        <button class="btn btn-primary" onclick="openMaster()"><i class="fas fa-plus me-1"></i> Add {{ strtolower($def['singular']) }}</button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">#</th>@foreach($def['columns'] as $c)<th>{{ ucfirst(str_replace('_', ' ', $c)) }}</th>@endforeach<th>Active</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            @php
                $payload = [];
                foreach ($def['fields'] as $f) { $payload[$f['name']] = $row->{$f['name']}; }
                $payload['id'] = $row->id;
            @endphp
            <tr>
                <td class="ps-4">{{ $row->sort_order ?? '' }}</td>
                @foreach($def['columns'] as $c)<td>{{ $row->$c ?: '-' }}</td>@endforeach
                <td><span class="badge {{ $row->status ?? true ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ ($row->status ?? true) ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end pe-4 text-nowrap">
                    <button class="btn btn-sm btn-light border" data-row="{{ json_encode($payload) }}" onclick="openMaster(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                    @if($inUse[$row->id])
                        <button class="btn btn-sm btn-light border text-muted" disabled title="In use: mark inactive instead"><i class="fas fa-trash-alt"></i></button>
                    @else
                    <form action="{{ route('admin.counselling.masters.destroy', [$master, $row->id]) }}" method="POST" class="d-inline" data-confirm="Delete this {{ strtolower($def['singular']) }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($def['columns']) + 3 }}" class="text-center text-muted py-5">Nothing here yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>

<div class="modal fade" id="masterModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="masterForm" method="POST" action="{{ route('admin.counselling.masters.store', $master) }}">
        @csrf <span id="masterMethod"></span>
        <div class="modal-header"><h5 class="modal-title" id="masterTitle">{{ $def['singular'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            @foreach($def['fields'] as $f)
                @php $opts = in_array($f['type'], ['select', 'multiselect']) ? \App\Support\Counselling\Masters::options($f) : []; @endphp
                <div class="col-md-{{ $f['col'] ?? 12 }}">
                    @if($f['type'] === 'bool')
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="{{ $f['name'] }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $f['name'] }}" value="1" data-default="{{ !empty($f['default']) ? 1 : 0 }}">
                            <label class="form-check-label">{{ $f['label'] }}</label></div>
                    @else
                        <label class="form-label fw-bold">{{ $f['label'] }}@if(!empty($f['required'])) <span class="text-danger">*</span>@endif</label>
                        @if($f['type'] === 'textarea')<textarea name="{{ $f['name'] }}" rows="3" class="form-control"></textarea>
                        @elseif($f['type'] === 'number')<input type="number" min="0" name="{{ $f['name'] }}" class="form-control" data-default="{{ $f['default'] ?? '' }}">
                        @elseif($f['type'] === 'select')
                            <select name="{{ $f['name'] }}" class="form-select" data-default="{{ $f['default'] ?? '' }}"><option value="">Select</option>
                                @foreach($opts as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                        @elseif($f['type'] === 'multiselect')
                            <select name="{{ $f['name'] }}[]" class="form-select" multiple>@foreach($opts as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                        @else<input type="text" name="{{ $f['name'] }}" class="form-control">@endif
                    @endif
                </div>
            @endforeach
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')
<script>
    const masterStore = @json(route('admin.counselling.masters.store', $master));
    const masterBase = @json(url('admin/counselling/masters/' . $master));
    function openMaster(row) {
        const form = document.getElementById('masterForm');
        form.reset();
        form.querySelectorAll('[data-default]').forEach(el => {
            if (el.type === 'checkbox') el.checked = el.dataset.default === '1';
            else if (el.dataset.default !== '') el.value = el.dataset.default;
        });
        form.action = row ? masterBase + '/' + row.id : masterStore;
        document.getElementById('masterMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) {
            Object.entries(row).forEach(([name, val]) => {
                if (name === 'id') return;
                const multi = form.querySelector(`[name="${name}[]"]`);
                if (multi) { $(multi).val(val || []).trigger('change'); return; }
                const el = form.querySelector(`[name="${name}"]:not([type=hidden])`);
                if (!el) return;
                if (el.type === 'checkbox') el.checked = !!val; else el.value = val ?? '';
                $(el).trigger('change');
            });
        }
        new bootstrap.Modal(document.getElementById('masterModal')).show();
    }
    @if($errors->any()) $(function () { new bootstrap.Modal(document.getElementById('masterModal')).show(); }); @endif
</script>
@endpush
