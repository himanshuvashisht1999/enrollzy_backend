@extends('admin.layouts.master')

@section('title', 'Counselling')

@section('content')
@can('cns-masters-manage')
    @php $pendingFixes = app(\App\Services\Counselling\DataFixes::class)->pendingCount(); @endphp
    @if($pendingFixes)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><strong>{{ $pendingFixes }}</strong> item(s) in existing data need a one-time correction.</span>
            <a href="{{ route('admin.counselling.data-fixes.index') }}" class="btn btn-sm btn-warning">Review data fixes</a>
        </div>
    @endif
@endcan
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Counselling</h4>
        <p class="text-muted mb-0">Permanent admission counsellings. Dates, rules and rounds live in yearly cycles.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('cns-masters-manage')
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" data-bs-toggle="dropdown">Masters</button>
                <ul class="dropdown-menu">
                    @foreach(\App\Support\Counselling\Masters::all() as $key => $m)
                        <li><a class="dropdown-item" href="{{ route('admin.counselling.masters.index', $key) }}">{{ $m['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endcan
        @can('cns-codes-manage')<a href="{{ route('admin.counselling.codes.index') }}" class="btn btn-light border">Institute codes</a>@endcan
        <a href="{{ route('admin.counselling.alerts.index') }}" class="btn btn-light border"><i class="fas fa-bell me-1"></i> Alerts</a>
        @can('cns-import-manage')<a href="{{ route('admin.counselling.imports.index') }}" class="btn btn-light border"><i class="fas fa-file-csv me-1"></i> Imports</a>@endcan
        @can('cns-counsellings-add')<a href="{{ route('admin.counselling.counsellings.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Counselling</a>@endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, alias, slug"></div>
    <div class="col-md-2">
        <select name="level" class="form-select custom-select2"><option value="">All levels</option>
            @foreach(\App\Models\Counselling\Counselling::LEVELS as $k => $v)<option value="{{ $k }}" @selected(request('level') === $k)>{{ $v }}</option>@endforeach</select>
    </div>
    <div class="col-md-2">
        <select name="state" class="form-select custom-select2"><option value="">All states</option>
            @foreach(\App\Models\ExamModule\Exam::STATES as $s)<option @selected(request('state') === $s)>{{ $s }}</option>@endforeach</select>
    </div>
    <div class="col-md-2">
        <select name="program_level" class="form-select custom-select2"><option value="">All program levels</option>
            @foreach($levels as $id => $t)<option value="{{ $id }}" @selected(request('program_level') == $id)>{{ $t }}</option>@endforeach</select>
    </div>
    <div class="col-md-2">
        <select name="visibility" class="form-select custom-select2"><option value="">Draft + Public</option>
            <option value="draft" @selected(request('visibility') === 'draft')>Draft</option><option value="public" @selected(request('visibility') === 'public')>Public</option></select>
    </div>
    <div class="col-md-1"><button class="btn btn-light border w-100">Go</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Counselling</th><th>Level</th><th>Conducted by</th><th>Exams</th><th>Current cycle</th><th>Current round</th><th>Visibility</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($counsellings as $c)
            @php $cycle = $c->currentCycle; $round = $cycle?->currentRound(); @endphp
            <tr>
                <td class="ps-4">
                    <a href="{{ route('admin.counselling.counsellings.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a>
                    <div class="small text-muted">/{{ $c->slug }}</div>
                </td>
                <td>{{ \App\Models\Counselling\Counselling::LEVELS[$c->level] }}</td>
                <td>{{ $c->conductingBody->name ?? '-' }}</td>
                <td class="small">{{ $c->exams->pluck('name')->implode(', ') ?: '-' }}</td>
                <td>{{ $cycle?->title ?? '-' }}</td>
                <td>{{ $round?->name ?? '-' }}</td>
                <td>
                    <span class="badge {{ $c->visibility === 'public' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($c->visibility) }}</span>
                    @unless($c->is_active)<span class="badge bg-danger-subtle text-danger">Inactive</span>@endunless
                    @if($c->is_archived)<span class="badge bg-warning-subtle text-warning">Archived</span>@endif
                </td>
                <td class="text-end pe-4 text-nowrap">
                    <a href="{{ route('admin.counselling.counsellings.show', $c) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye text-info"></i></a>
                    @can('cns-counsellings-edit')<a href="{{ route('admin.counselling.counsellings.edit', $c) }}" class="btn btn-sm btn-light border"><i class="fas fa-edit text-primary"></i></a>@endcan
                    @can('cns-counsellings-delete')
                    <form action="{{ route('admin.counselling.counsellings.destroy', $c) }}" method="POST" class="d-inline" data-confirm="Delete this counselling? Its cycles and rounds are hidden with it.">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5">No counsellings yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $counsellings->links() }}</div>
@endsection
