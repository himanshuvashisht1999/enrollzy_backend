<div class="col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">{{ $title }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route($storeRoute) }}" method="POST" class="mb-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ $placeholder }}" required>
                    <button class="btn btn-primary btn-sm" type="submit">Add</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Name</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td class="align-middle">{{ $item->name }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-link text-info p-0 me-2" 
                                    onclick="editMaster('{{ $item->id }}', '{{ $item->name }}', '{{ route($updateRoute, $item->id) }}', '{{ $title }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route($destroyRoute, $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0" onclick="return confirm('Delete this record (Soft Delete)?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
