@extends('admin.layouts.master')

@section('title', 'Client Details - ' . $client->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 52px; height: 52px; font-size: 22px;">
            {{ strtoupper(substr($client->name, 0, 1)) }}
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">{{ $client->name }}</h4>
                @if($client->status)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                @endif
            </div>
            <div class="text-muted small">
                @if($client->company_type)
                    <span class="badge bg-secondary-subtle text-secondary border me-2">{{ $client->company_type }}</span>
                @endif
                <span>Client ID: #{{ str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.billing.clients.edit', $client->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Client
        </a>
        <a href="{{ route('admin.billing.clients.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Clients
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column: Details -->
    <div class="col-lg-8">
        <!-- Tax & Legal Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-file-invoice me-2"></i> Tax & Registration Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="text-muted small text-uppercase fw-bold d-block">GSTIN Number</label>
                        @if($client->gstin)
                            <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-2">{{ $client->gstin }}</span>
                        @else
                            <span class="text-muted fst-italic">Not provided</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small text-uppercase fw-bold d-block">TAN Number</label>
                        @if($client->tan_number)
                            <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-2">{{ $client->tan_number }}</span>
                        @else
                            <span class="text-muted fst-italic">Not provided</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small text-uppercase fw-bold d-block">PAN Number</label>
                        @if($client->pan_number)
                            <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-2">{{ $client->pan_number }}</span>
                        @else
                            <span class="text-muted fst-italic">Not provided</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small text-uppercase fw-bold d-block">CIN Number</label>
                        @if($client->cin_number)
                            <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-2">{{ $client->cin_number }}</span>
                        @else
                            <span class="text-muted fst-italic">Not provided</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Address Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-address-card me-2"></i> Contact & Address Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-user-circle me-1"></i> Contact Details</h6>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Contact Person:</span>
                            <span class="fw-medium text-dark">{{ $client->contact_person ?: '—' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Email Address:</span>
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="text-primary text-decoration-none fw-medium"><i class="fas fa-envelope me-1 text-muted"></i>{{ $client->email }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-muted small d-block">Phone Number:</span>
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="text-dark text-decoration-none fw-medium"><i class="fas fa-phone me-1 text-muted"></i>{{ $client->phone }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-map-marker-alt me-1"></i> Billing Address</h6>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Address:</span>
                            <span class="text-dark">{{ $client->address ?: '—' }}</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted small d-block">City / State:</span>
                                <span class="text-dark fw-medium">{{ implode(', ', array_filter([$client->city, $client->state])) ?: '—' }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Pincode:</span>
                                <span class="text-dark fw-medium">{{ $client->pincode ?: '—' }}</span>
                            </div>
                            <div class="col-12 mt-1">
                                <span class="text-muted small d-block">Country:</span>
                                <span class="text-dark fw-medium">{{ $client->country ?: 'India' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Meta & Actions -->
    <div class="col-lg-4">
        <!-- Internal Notes -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-sticky-note me-2 text-warning"></i> Internal Notes
                </h6>
            </div>
            <div class="card-body">
                @if($client->notes)
                    <p class="text-muted mb-0" style="white-space: pre-line;">{{ $client->notes }}</p>
                @else
                    <p class="text-muted fst-italic mb-0 small">No internal notes added for this client.</p>
                @endif
            </div>
        </div>

        <!-- Meta Details -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-info-circle me-2 text-info"></i> Record Information
                </h6>
            </div>
            <div class="card-body small text-muted">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Created Date:</span>
                    <span class="text-dark fw-medium">{{ $client->created_at ? $client->created_at->format('d M Y, h:i A') : '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Last Updated:</span>
                    <span class="text-dark fw-medium">{{ $client->updated_at ? $client->updated_at->format('d M Y, h:i A') : '—' }}</span>
                </div>
                <div class="pt-3">
                    <form action="{{ route('admin.billing.clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete client {{ addslashes($client->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                            <i class="fas fa-trash-alt me-1"></i> Delete Client
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
