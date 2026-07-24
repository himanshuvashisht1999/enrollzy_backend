@extends('admin.layouts.master')

@section('title', 'All Mentors')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">All Mentors</h4>
        <a href="{{ route('admin.mentor.profiles.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-1"></i> Add New Mentor
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Photo</th>
                            <th>Name</th>
                            <th>Headline</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mentors as $mentor)
                        @php
                            $mName = trim(($mentor->first_name ?? '') . ' ' . ($mentor->last_name ?? ''));
                            if (empty($mName)) {
                                $mName = $mentor->user->name ?? 'Mentor';
                            }
                            $fallbackAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($mName) . '&background=4e73df&color=ffffff&size=128';
                            $photoUrl = $fallbackAvatar;
                            if (!empty($mentor->profile_photo)) {
                                if (str_starts_with($mentor->profile_photo, 'http')) {
                                    $photoUrl = $mentor->profile_photo;
                                } elseif (file_exists(public_path('storage/' . $mentor->profile_photo))) {
                                    $photoUrl = asset('storage/' . $mentor->profile_photo);
                                } elseif (file_exists(public_path('assets/images/' . $mentor->profile_photo))) {
                                    $photoUrl = asset('assets/images/' . $mentor->profile_photo);
                                }
                            }
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <img src="{{ $photoUrl }}" alt="{{ $mName }}" class="rounded-circle border" style="width: 42px; height: 42px; object-fit: cover;" onError="this.onerror=null; this.src='{{ $fallbackAvatar }}';">
                            </td>
                            <td class="fw-bold">{{ $mName }}</td>
                            <td class="text-muted text-truncate" style="max-width: 220px;">{{ $mentor->professional_headline ?? '-' }}</td>
                            <td>{{ $mentor->city ? $mentor->city . ', ' . $mentor->state_country : '-' }}</td>
                            <td>
                                @php
                                    $v = $mentor->verification;
                                    $allVerified = $v && $v->gov_id_status == 'verified' && $v->linkedin_status == 'verified' && $v->background_check_status == 'verified' && $v->degree_status == 'verified' && $v->platform_agreement_signed;
                                    $anyPending = $v && ($v->gov_id_status == 'pending' || $v->linkedin_status == 'pending' || $v->background_check_status == 'pending' || $v->degree_status == 'pending');
                                @endphp
                                @if($allVerified)
                                    <span class="badge bg-success"><i class="bi bi-patch-check"></i> Verified</span>
                                @elseif($anyPending)
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">Active</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.mentor.profiles.show', $mentor->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3 me-1">View</a>
                                <a href="{{ route('admin.mentor.profiles.edit', $mentor->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1"><i class="fas fa-edit"></i> Edit</a>
                                <form action="{{ route('admin.mentor.profiles.destroy', $mentor->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to delete this mentor?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No mentors found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mentors->hasPages())
        <div class="card-footer border-0 bg-white pt-3 pb-3">
            {{ $mentors->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
