{{-- One row of a repeater: its sub-fields side by side (a nested repeater gets the full width). --}}
<div class="hs-row border rounded-3 p-3 mb-2 {{ $depth ? 'bg-white' : 'bg-light' }}">
    <div class="d-flex justify-content-end gap-1 mb-1">
        <button type="button" class="btn btn-sm btn-light border hs-up" title="Move up"><i class="fas fa-arrow-up"></i></button>
        <button type="button" class="btn btn-sm btn-light border hs-down" title="Move down"><i class="fas fa-arrow-down"></i></button>
        <button type="button" class="btn btn-sm btn-outline-danger hs-remove" title="Remove"><i class="fas fa-times"></i></button>
    </div>
    <div class="row g-2">
        @foreach($fields as $key => $sub)
            <div class="{{ $sub['type'] === 'repeater' ? 'col-12' : ($sub['type'] === 'icon' ? 'col-md-3' : 'col-md') }}">
                @include('admin.homepage-sections._field', ['f' => $sub, 'name' => $prefix . '[' . $key . ']', 'value' => $row[$key] ?? null, 'depth' => $depth + 1])
            </div>
        @endforeach
    </div>
</div>
