@extends('admin.layouts.master')

@section('title', 'Pending Mentor Verifications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Pending Mentor Verifications</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mentor Name</th>
                            <th>Gov ID</th>
                            <th>LinkedIn</th>
                            <th>Background Check</th>
                            <th>Degree</th>
                            <th>Agreement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $verification)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $verification->profile->user->name ?? 'Unknown' }}</td>
                            
                            <!-- Gov ID -->
                            <td>
                                @if($verification->gov_id_status == 'pending')
                                    <span class="badge bg-warning text-dark mb-2">Pending Review</span>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'gov_id']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2"><i class="bi bi-check"></i></button>
                                        </form>
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'gov_id']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="bi bi-x"></i></button>
                                        </form>
                                    </div>
                                    <a href="#" class="small mt-1 d-block">View File</a>
                                @else
                                    <span class="badge bg-{{ $verification->gov_id_status == 'verified' ? 'success' : 'secondary' }}">{{ ucfirst($verification->gov_id_status) }}</span>
                                @endif
                            </td>

                            <!-- LinkedIn -->
                            <td>
                                @if($verification->linkedin_status == 'pending')
                                    <span class="badge bg-warning text-dark mb-2">Pending</span>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'linkedin']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2"><i class="bi bi-check"></i></button>
                                        </form>
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'linkedin']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="bi bi-x"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-{{ $verification->linkedin_status == 'verified' ? 'success' : 'secondary' }}">{{ ucfirst($verification->linkedin_status) }}</span>
                                @endif
                            </td>

                            <!-- Background Check -->
                            <td>
                                @if($verification->background_check_status == 'pending')
                                    <span class="badge bg-warning text-dark mb-2">Initiated</span>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'background_check']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2">Clear</button>
                                        </form>
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'background_check']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-2">Fail</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-{{ $verification->background_check_status == 'verified' ? 'success' : 'secondary' }}">{{ ucfirst($verification->background_check_status) }}</span>
                                @endif
                            </td>

                            <!-- Degree -->
                            <td>
                                @if($verification->degree_status == 'pending')
                                    <span class="badge bg-warning text-dark mb-2">Pending</span>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'degree']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="verified">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2"><i class="bi bi-check"></i></button>
                                        </form>
                                        <form action="{{ route('admin.mentor.verifications.update', [$verification->id, 'degree']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="bi bi-x"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-{{ $verification->degree_status == 'verified' ? 'success' : 'secondary' }}">{{ ucfirst($verification->degree_status) }}</span>
                                @endif
                            </td>
                            
                            <!-- Agreement -->
                            <td>
                                @if($verification->platform_agreement_signed)
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Signed</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No pending verifications.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer border-0 bg-white pt-3 pb-3">
            {{ $verifications->links() }}
        </div>
    </div>
</div>
@endsection
