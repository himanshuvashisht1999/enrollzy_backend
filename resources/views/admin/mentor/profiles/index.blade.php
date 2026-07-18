@extends('admin.layouts.master')

@section('title', 'All Mentors')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Mentors</h4>
    </div>

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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mentors as $mentor)
                        <tr>
                            <td class="ps-4">
                                @if($mentor->profile_photo)
                                    <!-- Use a generic env URL or relative path since frontend and backend might be on different ports -->
                                    <img src="http://127.0.0.1:8000/{{ $mentor->profile_photo }}" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="width: 40px; height: 40px;">
                                        {{ substr($mentor->user->name ?? 'M', 0, 1) }}
                                    </div>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $mentor->user->name ?? 'Unknown' }}</td>
                            <td class="text-muted text-truncate" style="max-width: 200px;">{{ $mentor->professional_headline ?? '-' }}</td>
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
                                    <span class="badge bg-secondary">Unverified</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.mentor.profiles.show', $mentor->id) }}" class="btn btn-sm btn-primary px-3 rounded-pill">View</a>
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
        <div class="card-footer border-0 bg-white pt-3 pb-3">
            {{ $mentors->links() }}
        </div>
    </div>
</div>
@endsection
