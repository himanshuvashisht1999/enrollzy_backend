@extends('admin.layouts.master')

@section('title', 'Mega Menu Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Mega Dropdown Menu Management</h3>
            <p class="text-muted mb-0">
                Main categories are auto-synced from
                <a href="{{ route('admin.header-links.index') }}" class="text-primary fw-semibold">Header Links</a>.
                Manage sub-items (dropdown options) for each category below.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success px-4 shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addItemModal">
                <i class="fas fa-plus me-2"></i>Add Sub Option Link
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.mega-menu.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Parent Category</label>
                        <select name="parent_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- Select Parent --</option>
                            @foreach($parentLinks as $parent)
                                <option value="{{ $parent->id }}" {{ $selectedParent == $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($selectedParent)
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Child Category</label>
                        <select name="child_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- Select Child --</option>
                            @foreach($childLinks as $child)
                                <option value="{{ $child->id }}" {{ $selectedChild == $child->id ? 'selected' : '' }}>{{ $child->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @if(!$selectedChild)
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="fas fa-info-circle me-2"></i> Please select a Parent and then a Child category above to manage its Mega Menu.
                </div>
            </div>
        @endif
        @forelse($headerLinks as $hl)
            @php
                $items = $subItemsByHeaderLink->get($hl->id, collect());
                $groupedSubItems = $items->groupBy('column_title');
            @endphp
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">
                                <i class="fas fa-link me-1"></i> Header Link
                            </span>
                            <h5 class="fw-bold mb-0 text-dark">{{ strtoupper($hl->title) }}</h5>
                            @if($hl->url)
                                <small class="text-muted">→ {{ $hl->url }}</small>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $hl->status ? 'success' : 'secondary' }} rounded-pill">
                                {{ $hl->status ? 'Active' : 'Inactive' }}
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-success add-sub-btn"
                                data-header-link-id="{{ $hl->id }}"
                                data-header-link-title="{{ $hl->title }}">
                                <i class="fas fa-plus me-1"></i> Add Sub Option
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @if($groupedSubItems->count() > 0)
                            <div class="row g-3">
                                @foreach($groupedSubItems as $columnHeading => $colItems)
                                    <div class="col-md-4 col-lg-3">
                                        <div class="p-3 bg-white rounded border h-100">
                                            <h6 class="fw-bold text-uppercase text-primary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="fas fa-list-ul me-1"></i>
                                                    {{ $columnHeading ?: 'General Links' }}
                                                </span>
                                                <button type="button" class="btn btn-sm btn-link text-success p-0 add-col-sub-btn" 
                                                    title="Add link to this column"
                                                    data-header-link-id="{{ $hl->id }}" 
                                                    data-column-title="{{ $columnHeading }}">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($colItems as $item)
                                                    <li class="d-flex justify-content-between align-items-center py-1 border-bottom-light">
                                                        <div class="d-flex align-items-center gap-1">
                                                            @if($item->is_highlighted)
                                                                <span class="badge bg-warning text-dark" style="font-size:9px">NEW</span>
                                                            @endif
                                                            <a href="{{ $item->url ? url($item->url) : '#' }}" target="_blank"
                                                                class="text-decoration-none text-dark fw-medium small"
                                                                title="{{ $item->url }}">
                                                                {{ $item->title }}
                                                            </a>
                                                        </div>
                                                        <div class="d-flex gap-1 ms-2">
                                                            <button type="button" class="btn btn-link text-primary p-0 edit-item-btn"
                                                                data-id="{{ $item->id }}"
                                                                data-header-link-id="{{ $item->header_link_id }}"
                                                                data-column-title="{{ $item->column_title }}"
                                                                data-title="{{ $item->title }}"
                                                                data-url="{{ $item->url }}"
                                                                data-sort-order="{{ $item->sort_order }}"
                                                                data-is-highlighted="{{ $item->is_highlighted }}">
                                                                <i class="fas fa-pen fa-xs"></i>
                                                            </button>
                                                            <form action="{{ route('admin.mega-menu.destroy', $item->id) }}" method="POST"
                                                                onsubmit="return confirm('Delete this sub option?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-danger p-0 ms-1">
                                                                    <i class="fas fa-times fa-xs"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted small mb-2 fst-italic">No sub-options added yet for this category.</p>
                                <button type="button" class="btn btn-sm btn-outline-success add-sub-btn"
                                    data-header-link-id="{{ $hl->id }}"
                                    data-header-link-title="{{ $hl->title }}">
                                    <i class="fas fa-plus me-1"></i> Add First Sub Option
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-sitemap fa-3x text-light mb-3"></i>
                        <h5>No Header Links Found</h5>
                        <p class="text-muted">Please add header links first from
                            <a href="{{ route('admin.header-links.index') }}">Header Links Manager</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Datalist for Column Group Headings Autocomplete --}}
    <datalist id="existingColumnHeadings">
        @foreach($existingColumnHeadings as $heading)
            <option value="{{ $heading }}">
        @endforeach
    </datalist>

    {{-- Modal: Add Sub Option Link --}}
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.mega-menu.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom bg-light">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-plus-circle me-2 text-success"></i>Add Sub Option Link
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Category (from Header Links)</label>
                            <select name="header_link_id" id="addHeaderLinkId" class="form-select" required>
                                <option value="">-- Select Header Link --</option>
                                @foreach($headerLinks as $hl)
                                    <option value="{{ $hl->id }}">{{ strtoupper($hl->title) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Column Group Heading</label>
                            <input type="text" name="column_title" list="existingColumnHeadings" class="form-control"
                                placeholder="e.g. School Type, Browse by Stream" required autocomplete="off">
                            <div class="form-text">Links with the same heading will be grouped in one column.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sub Option Title</label>
                            <input type="text" name="title" class="form-control"
                                placeholder="e.g. Boys Boarding Schools" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Destination URL</label>
                            <input type="text" name="url" class="form-control"
                                placeholder="e.g. /all-schools?school_type=Boys+Boarding">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_highlighted" value="1" id="addHighlight">
                            <label class="form-check-label fw-medium" for="addHighlight">Highlight as New / Popular</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4">Add Sub Option</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit Sub Item --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom bg-light">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-edit me-2 text-primary"></i>Edit Sub Option
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Category</label>
                            <select name="header_link_id" id="edit_header_link_select" class="form-select" required>
                                @foreach($headerLinks as $hl)
                                    <option value="{{ $hl->id }}">{{ strtoupper($hl->title) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Column Group Heading</label>
                            <input type="text" name="column_title" id="edit_column_title"
                                list="existingColumnHeadings" class="form-control" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL</label>
                            <input type="text" name="url" id="edit_url" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_highlighted" value="1" id="edit_is_highlighted">
                            <label class="form-check-label fw-medium" for="edit_is_highlighted">Highlight as New / Popular</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update Sub Option</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function () {
                const headingsGroupedByHeaderLink = @json($headingsGroupedByHeaderLink ?? []);
                const allColumnHeadings = @json($existingColumnHeadings ?? []);

                function updateDatalistOptions(headerLinkId) {
                    const datalist = $('#existingColumnHeadings');
                    datalist.empty();
                    let list = (headerLinkId && headingsGroupedByHeaderLink[headerLinkId])
                        ? headingsGroupedByHeaderLink[headerLinkId]
                        : allColumnHeadings;
                    list.forEach(h => datalist.append(`<option value="${h}">`));
                }

                // Pre-select header_link_id when clicking "Add Sub Option" inline button
                $(document).on('click', '.add-sub-btn', function () {
                    const hlId = String($(this).attr('data-header-link-id'));
                    $('#addHeaderLinkId').val(hlId).trigger('change');
                    updateDatalistOptions(hlId);
                    $('input[name="column_title"]').first().val('');
                    $('#addItemModal').modal('show');
                });
                
                $(document).on('click', '.add-col-sub-btn', function () {
                    const hlId = String($(this).attr('data-header-link-id'));
                    const colTitle = $(this).attr('data-column-title');
                    $('#addHeaderLinkId').val(hlId).trigger('change');
                    updateDatalistOptions(hlId);
                    $('input[name="column_title"]').first().val(colTitle);
                    $('#addItemModal').modal('show');
                });

                $('#addHeaderLinkId').on('change', function () {
                    updateDatalistOptions($(this).val());
                });

                // Edit sub item — use .attr() for reliable reading
                let pendingEditValues = null;

                $(document).on('click', '.edit-item-btn', function () {
                    const btn = $(this);

                    pendingEditValues = {
                        id:            btn.attr('data-id'),
                        headerLinkId:  btn.attr('data-header-link-id'),
                        columnTitle:   btn.attr('data-column-title'),
                        title:         btn.attr('data-title'),
                        url:           btn.attr('data-url'),
                        sortOrder:     btn.attr('data-sort-order'),
                        isHighlighted: btn.attr('data-is-highlighted'),
                    };

                    $('#editForm').attr('action', `{{ url('admin/mega-menu') }}/${pendingEditValues.id}`);
                    $('#editModal').modal('show');
                });

                $('#editModal').on('shown.bs.modal', function () {
                    if (!pendingEditValues) return;
                    const v = pendingEditValues;
                    const targetHlId = String(v.headerLinkId);

                    $('#edit_header_link_select').val(targetHlId).trigger('change');
                    updateDatalistOptions(v.headerLinkId);
                    $('#edit_column_title').val(v.columnTitle !== 'null' ? (v.columnTitle || '') : '');
                    $('#edit_title').val(v.title || '');
                    $('#edit_url').val(v.url !== 'null' ? (v.url || '') : '');
                    $('#edit_sort_order').val(v.sortOrder || 0);
                    $('#edit_is_highlighted').prop('checked', v.isHighlighted === '1' || v.isHighlighted === 1);

                    pendingEditValues = null;
                });

                $('#editModal').on('hidden.bs.modal', function () {
                    $('#editForm')[0].reset();
                    pendingEditValues = null;
                });
            });
        </script>
    @endpush
@endsection