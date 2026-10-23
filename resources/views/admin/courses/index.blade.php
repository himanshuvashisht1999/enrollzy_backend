@extends('admin.layouts.master')

@section('title', 'Master Course List')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mt-2">
            <h3 class="fw-bold">Master Course List</h3>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create New Course
            </a>
        </div>
        <p class="text-muted mb-0 mt-2">Manage the global list of courses that universities can offer.</p>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3"><div class="card-body">
        <form action="{{ route('admin.courses.index') }}" method="GET" id="courseFilters">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-bold mb-1">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, full form or slug" value="{{ request('search') }}">
                </div>
                @foreach([
                    ['program_level_id', 'Program level', $filters['programLevels']],
                    ['stream_offered_id', 'Stream', $filters['streams']],
                    ['discipline_id', 'Discipline', $filters['disciplines']],
                    ['course_type_id', 'Course type', $filters['courseTypes']],
                    ['program_type_id', 'Program type', $filters['programTypes']],
                ] as [$name, $label, $options])
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small fw-bold mb-1">{{ $label }}</label>
                    <select name="{{ $name }}" class="form-select">
                        <option value="">All</option>
                        @if($name !== 'program_type_id')<option value="none" @selected(request($name) === 'none')>Not set</option>@endif
                        @foreach($options as $o)<option value="{{ $o->id }}" @selected((string) request($name) === (string) $o->id)>{{ $o->title }}</option>@endforeach
                    </select>
                </div>
                @endforeach
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small fw-bold mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('status') === '1')>Active</option>
                        <option value="0" @selected(request('status') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small fw-bold mb-1">Show on website</label>
                    <select name="website" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('website') === '1')>Yes</option>
                        <option value="0" @selected(request('website') === '0')>No</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small fw-bold mb-1">Sort by</label>
                    <select name="sort" class="form-select">
                        @foreach(['' => 'Sort order', 'name' => 'Name A–Z', 'name_desc' => 'Name Z–A', 'newest' => 'Newest first', 'oldest' => 'Oldest first'] as $k => $v)
                            <option value="{{ $k }}" @selected((string) request('sort') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 col-4">
                    <label class="form-label small fw-bold mb-1">Per page</label>
                    <select name="per_page" class="form-select">
                        @foreach([10, 25, 50, 100] as $n)<option value="{{ $n }}" @selected((int) request('per_page', 10) === $n)>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Apply</button>
                    @if($activeFilters || request('sort') || request('per_page'))
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-light border">Clear all</a>
                    @endif
                </div>
            </div>
        </form>
    </div></div>

    <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
        <span>
            {{ number_format($courses->total()) }} course{{ $courses->total() === 1 ? '' : 's' }}
            @if($activeFilters) found with {{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }} applied @endif
        </span>
        @if($courses->total())<span>Showing {{ number_format($courses->firstItem()) }}–{{ number_format($courses->lastItem()) }}</span>@endif
    </div>

    {{-- Bulk selection: the row checkboxes belong to this form (form="bulkForm"). Delete goes to a review page first. --}}
    <form method="POST" action="{{ route('admin.courses.bulk-review') }}" id="bulkForm">
        @csrf
        @foreach(request()->only(['search', 'program_level_id', 'stream_offered_id', 'discipline_id', 'course_type_id', 'program_type_id', 'status', 'website', 'sort', 'per_page', 'page']) as $k => $v)
            @if($v !== null && $v !== '')<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
        @endforeach
        <input type="hidden" name="all_matching" id="allMatching" value="0">
        <div id="bulkBar" class="alert alert-primary d-none d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 mb-2">
            <span>
                <strong id="bulkCount">0</strong> selected
                <span id="bulkAllNote" class="d-none">(all {{ number_format($courses->total()) }} courses matching the filters)</span>
                @if($courses->total() > $courses->count())
                    <button type="button" id="bulkSelectAllMatching" class="btn btn-link btn-sm p-0 ms-2 align-baseline d-none">Select all {{ number_format($courses->total()) }} matching courses</button>
                @endif
                <button type="button" id="bulkClear" class="btn btn-link btn-sm p-0 ms-2 align-baseline">Clear selection</button>
            </span>
            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete selected</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 36px"><input type="checkbox" class="form-check-input" id="checkAll" title="Select all on this page" aria-label="Select all courses on this page"></th>
                            <th>Course Name</th>
                            <th>Slug</th>
                            <th>Level</th>
                            <th>Stream</th>
                            <th>Discipline</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Show on Website</th>
                            <th>Sort Order</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td class="ps-4"><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $course->id }}" form="bulkForm" aria-label="Select {{ $course->name }}"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark">{{ $course->name }}</span>
                                        @if(!empty($course->faqs) && is_array($course->faqs) && count($course->faqs) > 0)
                                            <span class="badge bg-light text-primary border" title="{{ count($course->faqs) }} FAQs configured">
                                                <i class="fas fa-question-circle me-1"></i>{{ count($course->faqs) }} FAQs
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td><code class="text-muted">{{ $course->slug }}</code></td>
                                <td class="small">{{ $course->programLevel->title ?? '-' }}</td>
                                <td class="small">{{ $course->streamOffered->title ?? '-' }}</td>
                                <td class="small">{{ $course->discipline->title ?? '-' }}</td>
                                <td class="small">{{ $course->courseType->title ?? '-' }}</td>
                                <td>
                                    @if($course->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($course->is_show_on_website)
                                        <span class="badge bg-info text-dark">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $course->sort_order }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.courses.edit', $course->id) }}"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.courses.duplicate', $course->id) }}"
                                        class="btn btn-sm btn-outline-secondary me-1" title="Duplicate Course">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST"
                                        class="d-inline" data-confirm="Delete {{ $course->name }}? This also deletes all university associations for this course.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">@if($activeFilters) No course matches these filters. <a href="{{ route('admin.courses.index') }}">Clear all</a> @else No courses found in the master list. @endif</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($courses->hasPages())
            <div class="card-footer bg-white">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const all = document.getElementById('checkAll');
        const rows = () => [...document.querySelectorAll('.row-check')];
        const bar = document.getElementById('bulkBar'), count = document.getElementById('bulkCount');
        const allMatching = document.getElementById('allMatching'), allNote = document.getElementById('bulkAllNote');
        const selectAllBtn = document.getElementById('bulkSelectAllMatching');
        const total = {{ (int) $courses->total() }};
        function refresh() {
            const ticked = rows().filter(r => r.checked).length;
            const everything = allMatching.value === '1';
            count.textContent = everything ? total.toLocaleString('en-IN') : ticked;
            bar.classList.toggle('d-none', ticked === 0 && !everything);
            all.checked = ticked > 0 && ticked === rows().length;
            all.indeterminate = ticked > 0 && ticked < rows().length;
            allNote.classList.toggle('d-none', !everything);
            if (selectAllBtn) selectAllBtn.classList.toggle('d-none', everything || ticked !== rows().length || ticked === 0);
        }
        if (!all) return;
        all.addEventListener('change', () => { rows().forEach(r => r.checked = all.checked); if (!all.checked) allMatching.value = '0'; refresh(); });
        rows().forEach(r => r.addEventListener('change', () => { if (!r.checked) allMatching.value = '0'; refresh(); }));
        if (selectAllBtn) selectAllBtn.addEventListener('click', () => { allMatching.value = '1'; refresh(); });
        document.getElementById('bulkClear').addEventListener('click', () => { rows().forEach(r => r.checked = false); allMatching.value = '0'; refresh(); });
        refresh();
    })();
</script>
@endpush