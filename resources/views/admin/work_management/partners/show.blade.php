@extends('admin.layouts.master')

@section('title', 'Partner: ' . $partner->name)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.dashboard.overview') }}">Work Management</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work_management.partners.index') }}">External Partners</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $partner->name }}</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.work_management.partners.edit', encrypt($partner->id)) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-edit me-1"></i> Edit Partner
            </a>
        </div>
    </div>

    <!-- Partner Header -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge bg-soft-info text-info fs-6 px-3 py-1 text-uppercase">{{ $partner->organization_type }}</span>
                        <h3 class="fw-bold text-dark m-0">{{ $partner->name }}</h3>
                        {!! GetStatusBadge($partner->status) !!}
                    </div>
                    <p class="text-muted mb-3">{{ $partner->description ?: 'No additional notes provided.' }}</p>
                    <div class="d-flex flex-wrap gap-4 text-muted small">
                        @if($partner->email)
                            <div><i class="fas fa-envelope text-primary me-1"></i> {{ $partner->email }}</div>
                        @endif
                        @if($partner->phone)
                            <div><i class="fas fa-phone text-success me-1"></i> {{ $partner->phone }}</div>
                        @endif
                        @if($partner->website)
                            <div><i class="fas fa-globe text-info me-1"></i> <a href="{{ $partner->website }}" target="_blank" class="text-decoration-none">{{ $partner->website }}</a></div>
                        @endif
                        @if($partner->address)
                            <div><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $partner->address }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="fw-bold text-primary mb-0">{{ $partner->contacts->count() }}</h4>
                                <small class="text-muted">Contacts</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="fw-bold text-info mb-0">{{ $partner->teams->count() }}</h4>
                                <small class="text-muted">External Teams</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Contacts and External Teams -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <ul class="nav nav-pills" id="partnerTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 py-2 me-2" id="contacts-tab" data-bs-toggle="pill" data-bs-target="#contactsTab" type="button">
                        <i class="fas fa-address-book me-1"></i> Contacts Roster ({{ $partner->contacts->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 py-2" id="teams-tab" data-bs-toggle="pill" data-bs-target="#teamsTab" type="button">
                        <i class="fas fa-users-cog me-1"></i> External Teams ({{ $partner->teams->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 pt-2">
            <div class="tab-content" id="partnerTabContent">
                <!-- Contacts Tab -->
                <div class="tab-pane fade show active" id="contactsTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0 text-dark">Authorized Partner Contacts</h6>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addContactModal">
                            <i class="fas fa-plus me-1"></i> Add Contact
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light small">
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partner->contacts as $contact)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs rounded-circle bg-info text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                                </div>
                                                <div class="fw-bold text-dark">{{ $contact->name }}</div>
                                            </div>
                                        </td>
                                        <td class="small text-muted">{{ $contact->designation ?: 'Partner Representative' }}</td>
                                        <td class="small">{{ $contact->email ?: '-' }}</td>
                                        <td class="small text-muted">{{ $contact->phone ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No contact persons registered for this partner yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Teams Tab -->
                <div class="tab-pane fade" id="teamsTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0 text-dark">External Working Teams</h6>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                            <i class="fas fa-plus me-1"></i> Add External Team
                        </button>
                    </div>

                    <div class="row g-3">
                        @forelse($partner->teams as $eTeam)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border rounded-3 p-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0">{{ $eTeam->name }}</h6>
                                        <span class="badge bg-soft-info text-info">{{ $eTeam->members->count() }} staff</span>
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        Leader: <strong>{{ $eTeam->leaderContact->name ?? 'No Contact Leader' }}</strong>
                                    </small>
                                    <p class="small text-muted mb-0">{{ $eTeam->description ?: 'No team description.' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                <p class="mb-0">No external sub-teams registered for {{ $partner->name }}.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-user-plus text-primary me-2"></i> Add Contact Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.work_management.partners.add_contact', encrypt($partner->id)) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" required placeholder="e.g. Sarah Connor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Designation / Role</label>
                        <input type="text" name="designation" class="form-control rounded-3" placeholder="e.g. Senior QA Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="sarah@agency.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="+1 ...">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Save Contact</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add External Team -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-users-cog text-primary me-2"></i> Add External Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.work_management.partners.add_team', encrypt($partner->id)) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" required placeholder="e.g. Design Studio Squad">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Team Leader Contact</label>
                        <select name="team_leader_contact_id" class="form-select rounded-3">
                            <option value="">Select Contact Person (Optional)</option>
                            @foreach($partner->contacts as $cnt)
                                <option value="{{ $cnt->id }}">{{ $cnt->name }} ({{ $cnt->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Team Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Responsibilities of this squad..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Register Team</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
