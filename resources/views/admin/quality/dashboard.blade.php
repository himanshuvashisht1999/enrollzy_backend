@extends('admin.layouts.master')

@section('title', 'Rankings & Accreditation')

@php
    $tile = fn ($label, $value, $sub = null, $href = null, $tone = 'primary') => compact('label', 'value', 'sub', 'href', 'tone');
    $tiles = [
        $tile('Rankings', $stats['rankings'], $stats['rankings_unverified'] . ' unverified', route('admin.quality.review', ['kind' => 'ranking'])),
        $tile('Accreditations & approvals', $stats['accreditations'], $stats['accreditations_unverified'] . ' unverified', route('admin.quality.review', ['kind' => 'accreditation'])),
        $tile('Placement figures', $stats['placements'], $stats['placements_unverified'] . ' unverified', route('admin.quality.review', ['kind' => 'placement'])),
        $tile('Reviews', $stats['reviews'], $stats['reviews_unverified'] . ' unverified', route('admin.quality.review', ['kind' => 'review'])),
        $tile('Awards', $stats['awards'], $stats['awards_unverified'] . ' unverified' . ($stats['awards_to_sort'] ? ', ' . number_format($stats['awards_to_sort']) . ' old entries to sort' : ''), route('admin.quality.review', ['kind' => 'award'])),
        $tile('Statutory approvals', $stats['approvals'], $stats['approvals_unverified'] . ' unverified', route('admin.quality.review', ['kind' => 'approval'])),
        $tile('Expiring in 90 days', $stats['expiring_90'] + $stats['approvals_expiring_90'], 'accreditations and approvals', null, ($stats['expiring_90'] + $stats['approvals_expiring_90']) ? 'warning' : 'success'),
        $tile('Past validity, still active', $stats['expired'], 'needs a renewal entry', null, $stats['expired'] ? 'danger' : 'success'),
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Rankings &amp; Accreditation</h4>
        <p class="text-muted mb-0">Every ranking and accreditation is recorded against the entity that was assessed (organisation, campus, department or course), with its year and a verification state.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.quality.approvals.coverage') }}" class="btn btn-light border"><i class="fas fa-clipboard-check me-1"></i> Approval coverage</a>
        @can('quality-import')<a href="{{ route('admin.quality.approvals.import') }}" class="btn btn-light border"><i class="fas fa-file-csv me-1"></i> Import approvals</a>@endcan
        @can('quality-import')<a href="{{ route('admin.quality.placements.import') }}" class="btn btn-light border"><i class="fas fa-file-csv me-1"></i> Import placements</a>@endcan
        @can('quality-import')<a href="{{ route('admin.quality.reviews.import') }}" class="btn btn-light border"><i class="fas fa-file-csv me-1"></i> Import reviews</a>@endcan
        <a href="{{ route('admin.quality.review') }}" class="btn btn-primary"><i class="fas fa-check-double me-1"></i> Review queue</a>
        @can('quality-masters-manage')
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" data-bs-toggle="dropdown">Masters</button>
                <ul class="dropdown-menu">
                    @foreach(\App\Support\Quality\Masters::all() as $key => $m)
                        <li><a class="dropdown-item" href="{{ route('admin.quality.masters.index', $key) }}">{{ $m['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ route('admin.quality.setup') }}" class="btn btn-light border" title="Fill in starter data and copy the old values; safe to run again">Data update</a>
        @endcan
        @can('quality-identity-manage')
            <a href="{{ route('admin.quality.identity.index') }}" class="btn btn-light border">Identity review</a>
            <a href="{{ route('admin.quality.groups.index') }}" class="btn btn-light border">Groups</a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($tiles as $t)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <div class="text-muted small">{{ $t['label'] }}</div>
                <div class="fs-2 fw-bold text-{{ $t['tone'] }}">{{ number_format($t['value']) }}</div>
                <div class="small text-muted">{{ $t['sub'] }}@if($t['href']) · <a href="{{ $t['href'] }}">review</a>@endif</div>
            </div></div>
        </div>
    @endforeach
</div>

@if($stats['rankings_legacy'] || $stats['accreditations_legacy'])
    <div class="alert alert-warning">
        <strong>{{ $stats['rankings_legacy'] + $stats['accreditations_legacy'] }} records were migrated from the old columns</strong>
        ({{ $stats['rankings_legacy'] }} rankings, {{ $stats['accreditations_legacy'] }} accreditations). They have no year or source, so they are shown as unverified.
        Open each organisation's <em>Rankings &amp; Accreditation</em> page, set the year, add a source link and verify.
        @if($stats['no_year'])<br><span class="small">{{ $stats['no_year'] }} rankings still have no year.</span>@endif
    </div>
@endif

<div class="row g-3">
    @foreach([['Expiring in the next 90 days', $expiring, 'warning'], ['Past validity but still marked active', $expired, 'danger']] as [$title, $list, $tone])
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 fw-bold">{{ $title }}</div>
                <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                    <tbody>
                    @forelse($list as $a)
                        <tr>
                            <td class="ps-3"><a href="{{ route('admin.quality.entity', [$a->entity_type, $a->entity_id]) }}">{{ $names[$a->entity_type][$a->entity_id] ?? ucfirst($a->entity_type) . ' #' . $a->entity_id }}</a>
                                <div class="small text-muted">{{ \App\Support\Quality\Levels::LABELS[$a->entity_type] }}</div></td>
                            <td>{{ $a->body->short_name }} {{ $a->grade }}</td>
                            <td class="text-end pe-3"><span class="badge bg-{{ $tone }}-subtle text-{{ $tone }}">{{ $a->valid_to->format('d M Y') }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-muted py-4">None.</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4"><div class="card-body">
    <div class="fw-bold mb-2">Entity identity</div>
    <div class="row text-center">
        <div class="col"><div class="fs-4 fw-bold">{{ number_format($identity['orgs_total']) }}</div><div class="small text-muted">organisations</div></div>
        <div class="col"><div class="fs-4 fw-bold text-{{ $identity['orgs_no_aishe'] ? 'warning' : 'success' }}">{{ number_format($identity['orgs_no_aishe']) }}</div><div class="small text-muted">without AISHE code</div></div>
        <div class="col"><div class="fs-4 fw-bold text-{{ $identity['orgs_no_legal'] ? 'warning' : 'success' }}">{{ number_format($identity['orgs_no_legal']) }}</div><div class="small text-muted">without legal entity type</div></div>
    </div>
</div></div>
@endsection
