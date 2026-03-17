@extends('admin.layouts.master')

@section('title', 'Manage Homepage Sections')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-layer-group me-2"></i> Homepage Sections</h5>
                <small class="text-muted">Drag and drop to reorder sections. Toggle visibility to show/hide.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th width="50" class="text-center"><i class="fas fa-arrows-alt-v"></i></th>
                                <th>Section Name</th>
                                <th class="text-center" width="150">Visibility</th>
                                <th class="text-center" width="100">Order</th>
                            </tr>
                        </thead>
                        <tbody id="sections-list">
                            @foreach($sections as $section)
                                <tr data-id="{{ $section->id }}" class="sortable-item" style="cursor: move;">
                                    <td class="text-center text-muted"><i class="fas fa-grip-lines"></i></td>
                                    <td class="fw-bold">{{ $section->name }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('homepage-sections.update', $section->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input type="hidden" name="is_visible" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_visible" value="1" onchange="this.form.submit()" {{ $section->is_visible ? 'checked' : '' }}>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border order-badge">{{ $section->sort_order }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button class="btn btn-primary rounded-pill px-4" id="save-order">
                        <i class="fas fa-save me-2"></i> Save Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('sections-list');
        var sortable = Sortable.create(el, {
            animation: 150,
            handle: '.sortable-item',
            onEnd: function (evt) {
                updateBadgeNumbers();
            }
        });

        function updateBadgeNumbers() {
            var badges = document.querySelectorAll('.order-badge');
            badges.forEach(function(badge, index) {
                badge.textContent = index + 1;
            });
        }

        document.getElementById('save-order').addEventListener('click', function() {
            var order = sortable.toArray(); // This might use data-id attribute by default or I need to configure it
            // Sortable.toArray() returns array of data-id
            
            var orderedIds = [];
            document.querySelectorAll('#sections-list tr').forEach(function(row) {
                orderedIds.push(row.getAttribute('data-id'));
            });

            fetch("{{ route('homepage-sections.update-order') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ sections: orderedIds })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Order updated successfully!');
                    window.location.reload();
                } else {
                    alert('Something went wrong.');
                }
            });
        });
    });
</script>
@endpush
@endsection
