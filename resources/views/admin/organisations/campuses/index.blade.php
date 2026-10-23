@extends('admin.layouts.master')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Manage Campuses: {{ $organisation->name }} <span class="badge bg-warning text-dark ms-2 fs-6">{{ $campuses->count() }} Campuses</span></h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.organisations.edit', $organisation->id) }}"
                        class="btn btn-secondary btn-sm me-2">Back to Details</a>
                    <a href="{{ route('admin.organisations.campuses.create', $organisation->id) }}"
                        class="btn btn-primary btn-sm">+ Add New Campus</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($campuses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        @if($organisation->organisation_type_id == 4)
                                            <th>Type</th>
                                        @endif
                                        <th class="text-center">Departments</th>
                                        <th class="text-center">Courses</th>
                                        <th>Location</th>
                                        <th>Est. Year</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campuses as $campus)
                                        <tr>
                                            <td class="fw-bold">{{ $campus->campus_name }}</td>
                                            @if($organisation->organisation_type_id == 4)
                                                <td>
                                                    @if(is_array($campus->campus_type_new_id) && count($campus->campus_type_new_id) > 0)
                                                        @php
                                                            $campusTypeTitles = \App\Models\CampusTypeNew::whereIn('id', $campus->campus_type_new_id)->pluck('title')->toArray();
                                                        @endphp
                                                        @foreach($campusTypeTitles as $typeTitle)
                                                            <span class="badge bg-info">{{ $typeTitle }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                <a href="{{ route('admin.departments.index', ['organisation_id' => $organisation->id, 'campus_id' => $campus->id]) }}"
                                                    class="badge bg-primary text-white text-decoration-none px-2 py-1" title="View Departments">
                                                    <i class="fas fa-building me-1"></i>{{ $campus->departments_count }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.organisation-courses.index', ['organisation_id' => $organisation->id, 'campus_id' => $campus->id]) }}"
                                                    class="badge bg-success text-white text-decoration-none px-2 py-1" title="View Courses">
                                                    <i class="fas fa-graduation-cap me-1"></i>{{ $campus->courses_count }}
                                                </a>
                                            </td>
                                            <td>{{ $campus->city }}, {{ $campus->state }}</td>
                                            <td>{{ $campus->established_year }}</td>
                                            <td>
                                                @if($campus->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.organisations.campuses.edit', ['organisation' => $organisation->id, 'campus' => $campus->id]) }}"
                                                    class="btn btn-sm btn-outline-primary me-1" title="Edit Campus">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form
                                                    action="{{ route('admin.organisations.campuses.destroy', ['organisation' => $organisation->id, 'campus' => $campus->id]) }}"
                                                    method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this campus? All its departments and courses will also be deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h5 class="text-muted">No campuses found.</h5>
                            <p>Click "Add New Campus" to create the Main or Satellite campuses.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection