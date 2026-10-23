@extends('admin.layouts.master')

@section('title', 'Digital Business Cards')

@push('css')
<style>
    .stat-card-custom {
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .qr-thumb-btn {
        width: 44px;
        height: 44px;
        padding: 2px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .qr-thumb-btn:hover {
        transform: scale(1.1);
        border-color: #0f172a;
    }
    .copy-badge-btn {
        cursor: pointer;
        transition: all 0.2s;
    }
    .copy-badge-btn:hover {
        opacity: 0.85;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-id-card text-primary me-2"></i> Digital Business Cards
            </h3>
            <p class="text-muted small mb-0">Create, customize, generate QR codes and share smart digital business cards.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.digital-cards.create') }}" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Create Digital Card
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card-custom border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Total Cards</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ $stats['total_cards'] ?? 0 }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-id-badge fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card-custom border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Active Cards</span>
                        <h3 class="fw-bold text-success mt-1 mb-0">{{ $stats['active_cards'] ?? 0 }}</h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle p-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card-custom border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Total Views / Scans</span>
                        <h3 class="fw-bold text-info mt-1 mb-0">{{ number_format($stats['total_views'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle p-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-eye fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card-custom border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small text-uppercase">Contacts Saved (vCard)</span>
                        <h3 class="fw-bold text-warning mt-1 mb-0">{{ number_format($stats['total_downloads'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-address-book fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.digital-cards.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search by name, designation, company, phone, slug..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select bg-light">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-3 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.digital-cards.index') }}" class="btn btn-outline-secondary px-3">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>Profile</th>
                            <th>Public Link & QR</th>
                            <th>Companies / Brands</th>
                            <th>Analytics</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $loop->iteration + $cards->firstItem() - 1 }}</td>
                                
                                <!-- Profile -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $card->profile_image_url }}" alt="{{ $card->name }}" class="rounded-circle object-fit-cover me-3 border shadow-sm" width="48" height="48">
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $card->name }}</div>
                                            <div class="text-muted small">
                                                <span class="badge bg-light text-dark border">{{ $card->designation ?: 'N/A' }}</span>
                                                @if($card->company_name)
                                                    <span class="text-muted ms-1">&bull; {{ $card->company_name }}</span>
                                                @endif
                                            </div>
                                            @if($card->phone || $card->email)
                                                <div class="text-muted extra-small mt-1" style="font-size: 11px;">
                                                    @if($card->phone) <i class="fas fa-phone me-1"></i>{{ $card->phone }} @endif
                                                    @if($card->email) <i class="fas fa-envelope ms-2 me-1"></i>{{ $card->email }} @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Public Link & QR Code -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="qr-thumb-btn shadow-sm" onclick="showQrModal('{{ $card->name }}', '{{ $card->designation }}', '{{ $card->qr_code_url }}', '{{ $card->public_url }}')" title="Click to view & download QR code">
                                            <img src="{{ $card->qr_code_url }}" alt="QR" width="38" height="38" class="object-fit-contain">
                                        </button>
                                        <div>
                                            <a href="{{ $card->public_url }}" target="_blank" class="fw-semibold text-primary text-decoration-none small d-block">
                                                /card/{{ $card->slug }} <i class="fas fa-external-link-alt extra-small ms-1"></i>
                                            </a>
                                            <span class="badge bg-secondary-subtle text-secondary copy-badge-btn" onclick="copyToClipboard('{{ $card->public_url }}')">
                                                <i class="fas fa-copy me-1"></i> Copy URL
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Companies / Badges -->
                                <td>
                                    @if(!empty($card->companies) && is_array($card->companies))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_slice($card->companies, 0, 3) as $co)
                                                <span class="badge bg-light text-dark border small" title="{{ $co['subtitle'] ?? '' }}">
                                                    <i class="{{ $co['icon'] ?? 'fas fa-building' }} me-1" style="color: {{ $co['icon_color'] ?? '#c59b27' }}"></i>
                                                    {{ $co['name'] }}
                                                </span>
                                            @endforeach
                                            @if(count($card->companies) > 3)
                                                <span class="badge bg-secondary">+{{ count($card->companies) - 3 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">No sub-brands</span>
                                    @endif
                                </td>

                                <!-- Analytics -->
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-info-subtle text-info text-start">
                                            <i class="fas fa-eye me-1"></i> {{ $card->views_count }} Views
                                        </span>
                                        <span class="badge bg-warning-subtle text-warning text-start">
                                            <i class="fas fa-address-book me-1"></i> {{ $card->downloads_count }} vCards
                                        </span>
                                    </div>
                                </td>

                                <!-- Status Toggle -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" data-id="{{ $card->id }}" {{ $card->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ $card->public_url }}" target="_blank" class="btn btn-sm btn-outline-info" title="Preview Public Card">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.digital-cards.edit', $card->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Card">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.digital-cards.duplicate', $card->id) }}" class="btn btn-sm btn-outline-secondary" title="Duplicate Card">
                                            <i class="fas fa-clone"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ $card->id }}', '{{ addslashes($card->name) }}')" title="Delete Card">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $card->id }}" action="{{ route('admin.digital-cards.destroy', $card->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-id-card-clip fa-3x mb-3 text-secondary"></i>
                                    <h5>No Digital Cards Found</h5>
                                    <p class="small mb-3">Create your first digital business card with QR scanner and contact links.</p>
                                    <a href="{{ route('admin.digital-cards.create') }}" class="btn btn-primary btn-sm px-4">
                                        <i class="fas fa-plus me-1"></i> Create First Card
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($cards->hasPages())
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Showing {{ $cards->firstItem() }} to {{ $cards->lastItem() }} of {{ $cards->total() }} cards</span>
                    {{ $cards->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="adminQrModal" tabindex="-1" aria-labelledby="adminQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="qrModalName">Card Name</h5>
                    <p class="text-muted small mb-0" id="qrModalDesignation">Designation</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="p-3 bg-light rounded-4 d-inline-block border shadow-sm mb-3">
                    <img src="" id="qrModalImage" alt="QR Code" class="img-fluid" style="width: 220px; height: 220px;">
                </div>
                <p class="small text-muted mb-2">Scan with any smartphone camera to open this digital card.</p>
                <div class="input-group mb-3">
                    <input type="text" id="qrModalUrlInput" class="form-control form-control-sm text-center bg-light" readonly>
                    <button class="btn btn-outline-secondary btn-sm" onclick="copyModalUrl()"><i class="fas fa-copy me-1"></i> Copy</button>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <a href="#" id="qrDownloadBtn" class="btn btn-dark btn-sm px-3 rounded-3" download="card_qr.png" target="_blank">
                        <i class="fas fa-download me-1"></i> Download QR Image
                    </a>
                    <a href="#" id="qrPrintBtn" class="btn btn-outline-dark btn-sm px-3 rounded-3" onclick="printQrBadge()">
                        <i class="fas fa-print me-1"></i> Print Standee / Badge
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Card URL copied to clipboard: ' + text);
            });
        } else {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Card URL copied to clipboard: ' + text);
        }
    }

    function showQrModal(name, designation, qrUrl, publicUrl) {
        document.getElementById('qrModalName').innerText = name;
        document.getElementById('qrModalDesignation').innerText = designation || 'Digital Card';
        document.getElementById('qrModalImage').src = qrUrl;
        document.getElementById('qrModalUrlInput').value = publicUrl;
        document.getElementById('qrDownloadBtn').href = qrUrl;
        
        const modal = new bootstrap.Modal(document.getElementById('adminQrModal'));
        modal.show();
    }

    function copyModalUrl() {
        const input = document.getElementById('qrModalUrlInput');
        copyToClipboard(input.value);
    }

    function printQrBadge() {
        const qrUrl = document.getElementById('qrModalImage').src;
        const name = document.getElementById('qrModalName').innerText;
        const desig = document.getElementById('qrModalDesignation').innerText;
        const cardUrl = document.getElementById('qrModalUrlInput').value;

        const printWindow = window.open('', '_blank', 'width=600,height=700');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print QR Badge - ${name}</title>
                    <style>
                        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; padding: 40px; }
                        .badge-card { border: 2px solid #0f172a; border-radius: 20px; padding: 30px; max-width: 360px; margin: 0 auto; }
                        h2 { margin: 0 0 5px 0; color: #0f172a; font-size: 24px; }
                        h4 { margin: 0 0 20px 0; color: #c59b27; font-size: 16px; font-weight: normal; }
                        img { width: 220px; height: 220px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 15px; }
                        p { font-size: 13px; color: #64748b; margin: 5px 0 0 0; }
                        .url { font-weight: bold; color: #0f172a; word-break: break-all; margin-top: 10px; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="badge-card">
                        <h2>${name}</h2>
                        <h4>${desig}</h4>
                        <img src="${qrUrl}" alt="QR Code" />
                        <p>Scan with phone camera to save contact & view profile</p>
                        <div class="url">${cardUrl}</div>
                    </div>
                    <script>
                        window.onload = function() { window.print(); window.close(); };
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }

    function confirmDelete(id, name) {
        if (confirm('Are you sure you want to delete the digital card for "' + name + '"? This cannot be undone.')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

    // Ajax status toggle
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const cardId = this.dataset.id;
            fetch('{{ url("admin/digital-cards") }}/' + cardId + '/toggle-status', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    // status toggled
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to update card status.');
            });
        });
    });
</script>
@endpush
@endsection
