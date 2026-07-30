@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">All Organisations</h4>
                        <a href="{{ route('admin.organisations.create') }}" class="btn btn-primary">Add Organisation</a>
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" class="mb-3">
                            <div class="row gx-2">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name..."
                                        value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select name="organisation_type_id" class="form-select select2">
                                        <option value="">All Types</option>
                                        @if(isset($organisationTypes))
                                            @foreach($organisationTypes as $type)
                                                <option value="{{ $type->id }}" {{ request('organisation_type_id') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->title }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select name="per_page" class="form-select select2">
                                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                                </div>
                            </div>
                        </form>

                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    {{-- <th>Org ID</th> --}}
                                    <th>Organisation Name</th>
                                    <th>Type</th>
                                    <th>Brand Type</th>
                                    <th>Central Authority</th>
                                    <th>Head Office</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organisations as $uni)
                                    <tr>
                                        {{-- <td>{{ $uni->organisation_id_number ?? '-' }}</td> --}}
                                        <td>
                                            {{ $uni->name }}
                                            @if($uni->is_top)
                                                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-star text-dark me-1"></i>Top</span>
                                            @endif
                                        </td>
                                        <td>{{ $uni->organisationType->title ?? '-' }}</td>
                                        <td>
                                            @if($uni->brand_type)
                                                <span class="badge bg-info text-dark">{{ $uni->brand_type }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $uni->central_authority ?? '-' }}</td>
                                        <td>{{ $uni->head_office_location ?? '-' }}</td>
                                        <td class="text-end text-nowrap">
                                            <form action="{{ route('admin.organisations.toggle-status', $uni->id) }}" method="POST" class="d-inline" title="{{ $uni->status ? 'Unpublish' : 'Publish' }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $uni->status ? 'btn-success' : 'btn-secondary' }} me-2">
                                                    <i class="fas {{ $uni->status ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.organisations.campuses.index', $uni->id) }}"
                                                class="btn btn-sm btn-warning text-dark me-2" title="Campuses">
                                                <i class="fas fa-city"></i>
                                            </a>
                                            <a href="{{ route('admin.organisations.edit', $uni->id) }}"
                                                class="btn btn-sm btn-info text-white me-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.organisations.destroy', $uni->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No organisations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $organisations->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Select an option"
        });
        
        // Auto-submit form when a select changes
        $('.select2').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush