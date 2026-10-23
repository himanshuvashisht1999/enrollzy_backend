@extends('admin.layouts.master')

@section('title', 'Organisation groups')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Organisation groups</h4>
        <p class="text-muted mb-0">A brand that owns several legally separate universities (Amity, SRM...). Grouping links them for display only - each university keeps its own NAAC, NIRF and approvals.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.quality.moves.index') }}" class="btn btn-light border">Split a brand</a>
        <a href="{{ route('admin.quality.identity.index') }}" class="btn btn-light border">Identity review</a>
        <button class="btn btn-primary" onclick="openGroup()"><i class="fas fa-plus me-1"></i> Add group</button>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Group</th><th>Organisations</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($groups as $g)
            <tr>
                <td class="ps-4 fw-bold">{{ $g->name }}@if($g->website)<div class="small fw-normal"><a href="{{ $g->website }}" target="_blank" rel="noopener">{{ $g->website }}</a></div>@endif</td>
                <td>@forelse($g->organisations as $o)<a class="badge bg-light text-dark border me-1" href="{{ route('admin.organisations.edit', $o->id) }}">{{ $o->name }}</a>@empty<span class="text-muted">none yet</span>@endforelse</td>
                <td><span class="badge {{ $g->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $g->status ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end pe-4 text-nowrap">
                    <button class="btn btn-sm btn-light border" data-row="{{ json_encode($g->only(['id', 'name', 'website', 'notes', 'status'])) }}" onclick="openGroup(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                    @if($g->organisations_count)
                        <button class="btn btn-sm btn-light border text-muted" disabled title="Has organisations"><i class="fas fa-trash-alt"></i></button>
                    @else
                        <form action="{{ route('admin.quality.groups.destroy', $g) }}" method="POST" class="d-inline" data-confirm="Delete this group?">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">No groups yet. Add Amity, SRM, etc., then assign each university to its group on the Identity review screen.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>

<div class="modal fade" id="groupModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="groupForm" method="POST" action="{{ route('admin.quality.groups.store') }}">
        @csrf <span id="groupMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Group</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label fw-bold">Name <span class="text-danger">*</span></label><input name="name" class="form-control" required></div>
            <div class="col-12"><label class="form-label fw-bold">Website</label><input type="url" name="website" class="form-control" placeholder="https://"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
            <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="status" value="0"><input class="form-check-input" type="checkbox" name="status" value="1" checked><label class="form-check-label">Active</label></div></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')
<script>
    const groupStore = @json(route('admin.quality.groups.store'));
    const groupBase = @json(url('admin/quality/groups'));
    function openGroup(row) {
        const f = document.getElementById('groupForm');
        f.reset();
        f.action = row ? groupBase + '/' + row.id : groupStore;
        document.getElementById('groupMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) {
            f.name.value = row.name || ''; f.website.value = row.website || ''; f.notes.value = row.notes || '';
            f.querySelector('input[type=checkbox][name=status]').checked = !!row.status;
        }
        new bootstrap.Modal(document.getElementById('groupModal')).show();
    }
    @if($errors->any()) $(function () { new bootstrap.Modal(document.getElementById('groupModal')).show(); }); @endif
</script>
@endpush
