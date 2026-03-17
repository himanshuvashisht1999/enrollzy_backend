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
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by name..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
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
                                        <td>{{ $uni->name }}</td>
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