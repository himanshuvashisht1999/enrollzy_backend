@extends('admin.layouts.master')

@section('title', 'Exam Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Exams</h4>
        <p class="text-muted mb-0">Permanent exam identities. Dates and details live in yearly cycles.</p>
    </div>
    <div class="d-flex gap-2">
        @can('exm-attention-browse')<a href="{{ route('admin.exam-module.attention') }}" class="btn btn-outline-warning"><i class="fas fa-bell me-1"></i> Needs Attention</a>@endcan
        {{-- Conducting bodies are organisations of type "Exam Conducting Body" now (Organisations page), so no separate list here. --}}
        <a href="{{ route('admin.organisations.index', ['organisation_type_id' => \App\Models\Organisation::TYPE_EXAM_CONDUCTING_BODY]) }}" class="btn btn-light border">Conducting Bodies</a>
        @can('exm-event-types-manage')<a href="{{ route('admin.exam-module.event-types.index') }}" class="btn btn-light border">Event Types</a>@endcan
        @can('exm-cities-manage')<a href="{{ route('admin.exam-module.cities.index') }}" class="btn btn-light border">Cities</a>@endcan
        @can('exm-audit-browse')<a href="{{ route('admin.exam-module.audit') }}" class="btn btn-light border">Audit Log</a>@endcan
        @can('exm-exams-add')<a href="{{ route('admin.exam-module.exams.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Exam</a>@endcan
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, short name, alias, slug"></div>
    <div class="col-md-3">
        <select name="exam_type" class="form-select custom-select2">
            <option value="">All types</option>
            @foreach(\App\Models\ExamModule\Exam::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('exam_type') === $k)>{{ $v }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="visibility" class="form-select custom-select2">
            <option value="">Draft + Public</option>
            <option value="draft" @selected(request('visibility') === 'draft')>Draft</option>
            <option value="public" @selected(request('visibility') === 'public')>Public</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-light border w-100">Filter</button></div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr><th class="ps-4">Exam</th><th>Type</th><th>Conducted by</th><th>Cycles</th><th>Visibility</th><th class="text-end pe-4">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($exams as $exam)
                <tr>
                    <td class="ps-4">
                        <a href="{{ route('admin.exam-module.exams.show', $exam) }}" class="fw-semibold text-decoration-none">{{ $exam->name }}</a>
                        <div class="small text-muted">/{{ $exam->slug }}</div>
                    </td>
                    <td>{{ \App\Models\ExamModule\Exam::TYPES[$exam->exam_type] ?? '-' }}</td>
                    <td>{{ $exam->conductingBody->name ?? ($exam->source_type === 'internal' ? 'Owning organisation' : '-') }}</td>
                    <td class="exm-cycles">
                        @php $main = $exam->cycles->firstWhere('is_current', true) ?? $exam->cycles->first(); @endphp
                        @if($main)
                            <div class="d-flex align-items-center gap-1 flex-nowrap">
                                <span class="fw-semibold text-nowrap">{{ $main->title }}</span>
                                @if($main->is_current)<span class="badge bg-primary-subtle text-primary">Current</span>@endif
                                @unless($main->is_public)<span class="badge bg-warning-subtle text-warning" title="Admin only: this cycle's dates and details are not on the website">Not on website</span>@endunless
                            </div>
                        @endif
                        @can('exm-cycles-manage')
                        <div class="d-flex gap-1 mt-1">
                            @if($main)
                                <a href="{{ route('admin.exam-module.cycles.show', $main) }}" class="btn btn-sm btn-light border text-nowrap" title="Dates, events, eligibility, pattern, papers..."><i class="fas fa-calendar-alt text-primary me-1"></i>Dates &amp; content</a>
                                <a href="{{ route('admin.exam-module.cycles.edit', $main) }}" class="btn btn-sm btn-light border" title="Edit cycle settings (year, status, registration, result mode)"><i class="fas fa-sliders-h text-secondary"></i></a>
                            @endif
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle text-nowrap" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                    {{ $exam->cycles->count() > 1 ? 'All ' . $exam->cycles->count() : ($main ? 'Cycles' : 'Add cycle') }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:300px">
                                    @foreach($exam->cycles as $c)
                                        <li class="d-flex align-items-center gap-2 px-3 py-1">
                                            <div class="flex-grow-1">
                                                <span class="fw-semibold">{{ $c->title }}</span>
                                                @if($c->is_current)<span class="badge bg-primary-subtle text-primary">Current</span>@endif
                                                <span class="badge {{ $c->is_public ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $c->is_public ? 'Public' : 'Not on website' }}</span>
                                                <div class="small text-muted">{{ \App\Models\ExamModule\Cycle::STATUSES[$c->cycle_status] ?? $c->cycle_status }} · {{ $c->events_count }} {{ Str::plural('event', $c->events_count) }}</div>
                                            </div>
                                            <a href="{{ route('admin.exam-module.cycles.show', $c) }}" class="btn btn-sm btn-light border" title="Dates & content"><i class="fas fa-calendar-alt text-primary"></i></a>
                                            <a href="{{ route('admin.exam-module.cycles.edit', $c) }}" class="btn btn-sm btn-light border" title="Edit cycle settings"><i class="fas fa-sliders-h text-secondary"></i></a>
                                        </li>
                                    @endforeach
                                    @if($exam->cycles->count())<li><hr class="dropdown-divider"></li>@endif
                                    <li><a class="dropdown-item" href="{{ route('admin.exam-module.cycles.create', $exam) }}"><i class="fas fa-plus text-primary me-1"></i> Add cycle</a></li>
                                </ul>
                            </div>
                        </div>
                        @else
                            @unless($main)<span class="text-muted">-</span>@endunless
                        @endcan
                    </td>
                    <td>
                        <span class="badge {{ $exam->visibility === 'public' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($exam->visibility) }}</span>
                        @unless($exam->status)<span class="badge bg-danger-subtle text-danger">Inactive</span>@endunless
                        @if($exam->is_archived)<span class="badge bg-warning-subtle text-warning">Archived</span>@endif
                        @if($exam->featured)<span class="badge bg-primary-subtle text-primary">Featured</span>@endif
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.exam-module.exams.show', $exam) }}" class="btn btn-sm btn-light border"><i class="fas fa-eye text-info"></i></a>
                        @can('exm-exams-edit')<a href="{{ route('admin.exam-module.exams.edit', $exam) }}" class="btn btn-sm btn-light border"><i class="fas fa-edit text-primary"></i></a>@endcan
                        @can('cns-counsellings-browse')<a href="{{ route('admin.counselling.exams.counselling', $exam) }}" class="btn btn-sm btn-light border" title="Counselling mapped to this exam"><i class="fas fa-people-arrows text-success"></i> Counselling</a>@endcan
                        @can('exm-exams-delete')
                        <form action="{{ route('admin.exam-module.exams.destroy', $exam) }}" method="POST" class="d-inline" data-confirm="Delete this exam?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No exams yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $exams->links() }}</div>
@endsection
