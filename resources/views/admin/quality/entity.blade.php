@extends('admin.layouts.master')

@section('title', 'Rankings & Accreditation: ' . $name)

@php
    $canEdit = auth('admin')->user()?->can('quality-edit');
    $canVerify = auth('admin')->user()?->can('quality-verify');
    $badge = fn ($s) => ['verified' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger', 'unverified' => 'bg-warning-subtle text-warning'][$s] ?? 'bg-light text-dark';
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <div class="text-muted small">{{ $context }}</div>
        <h4 class="mb-0 fw-bold">{{ $name }}</h4>
        <p class="text-muted mb-0">Rankings &amp; accreditation recorded at <strong>{{ strtolower(\App\Support\Quality\Levels::LABELS[$type]) }}</strong> level.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $editUrl }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Back to {{ strtolower(\App\Support\Quality\Levels::LABELS[$type]) }}</a>
        <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

@foreach($inherited as $inh)
    @if($inh['accreditations']->isNotEmpty() || $inh['approvals']->isNotEmpty() || $inh['rankings']->isNotEmpty())
        <div class="alert alert-light border small">
            <strong>From the {{ strtolower($inh['label']) }}</strong> (<a href="{{ $inh['url'] }}">{{ $inh['name'] }}</a>) - shown on this page for context, recorded there, not copied here:
            @foreach($inh['accreditations'] as $a)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $inh['label'] }} {{ $a->body->short_name }} {{ $a->grade }}</span>@endforeach
            @foreach($inh['approvals'] as $ap)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $inh['label'] }} {{ $ap->body->short_name }} {{ $ap->academic_year }}@if($ap->sanctioned_intake !== null) &middot; {{ number_format($ap->sanctioned_intake) }} seats @endif</span>@endforeach
            @foreach($inh['rankings'] as $r)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $inh['label'] }} {{ $r->body->short_name ?: $r->body->name }} {{ $r->category->name }} {{ $r->rank_label }} @if($r->ranking_year)({{ $r->ranking_year }})@endif</span>@endforeach
        </div>
    @endif
@endforeach

{{-- Accreditations --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Accreditations, approvals &amp; recognitions</span>
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openAcc()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Body</th><th>Grade</th><th>Validity</th><th>Status</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($accreditations as $a)
            @php $p = $a->only(['id', 'accreditation_body_id', 'grade', 'score', 'cycle', 'status', 'certificate_number', 'source_url', 'notes']) + ['valid_from' => $a->valid_from?->toDateString(), 'valid_to' => $a->valid_to?->toDateString()]; @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $a->body->short_name }}<div class="small text-muted fw-normal">{{ \App\Models\Quality\AccreditationBody::KINDS[$a->body->kind] }}</div></td>
                <td>{{ $a->grade ?: '-' }}@if($a->score) <span class="small text-muted">({{ rtrim(rtrim(number_format($a->score, 2), '0'), '.') }})</span>@endif</td>
                <td>@if($a->valid_from || $a->valid_to){{ $a->valid_from?->format('d M Y') ?? '?' }} &rarr; {{ $a->valid_to?->format('d M Y') ?? 'open' }}@else<span class="text-muted">not recorded</span>@endif</td>
                <td><span class="badge {{ $a->effective_status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ \App\Models\Quality\EntityAccreditation::STATUSES[$a->effective_status] }}</span></td>
                <td><span class="badge {{ $badge($a->verification_status) }}">{{ ucfirst($a->verification_status) }}</span>@if($a->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $a->legacy_source }}">migrated</span>@endif</td>
                <td>@if($a->source_url)<a href="{{ $a->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($a->evidence_file)<a href="{{ route('admin.quality.evidence', ['accreditation', $a->id]) }}" target="_blank">file</a>@endif @if(!$a->source_url && !$a->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $a->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['accreditation', $a->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $a->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['accreditation', $a->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openAcc(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.accreditations.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this accreditation?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No accreditation recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

{{-- Awards & recognitions --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Awards &amp; recognitions <span class="text-muted fw-normal small">(who gave it, which year, and the evidence)</span></span>
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openAward()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    @if($awards->where('kind', '!=', 'award')->where('verification_status', '!=', 'rejected')->isNotEmpty())
        <div class="px-4 pb-2 small text-warning">Some entries here are not awards (rankings, memberships, affiliations...). They came from the old free-text list. Move each one to its proper place, or reject it; only real awards and memberships can be verified.</div>
    @endif
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Title</th><th>Type</th><th>Issuer</th><th>Year</th><th>Category</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($awards as $aw)
            @php $p = $aw->only(['id', 'title', 'kind', 'issuer', 'award_year', 'category', 'description', 'source_url', 'notes']); @endphp
            <tr class="{{ $aw->verification_status === 'rejected' ? 'text-muted' : '' }}">
                <td class="ps-4" style="max-width:380px">{{ $aw->title }}@if($aw->description)<div class="small text-muted">{{ \Illuminate\Support\Str::limit($aw->description, 100) }}</div>@endif</td>
                <td><span class="badge {{ $aw->kind === 'award' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}" title="{{ \App\Models\Quality\EntityAward::KINDS[$aw->kind] }}">{{ \App\Models\Quality\EntityAward::SHORT_KINDS[$aw->kind] }}</span></td>
                <td>{{ $aw->issuer ?: '-' }}</td>
                <td>{{ $aw->award_year ?: '-' }}</td>
                <td>{{ $aw->category ?: '-' }}</td>
                <td><span class="badge {{ $badge($aw->verification_status) }}">{{ ucfirst($aw->verification_status) }}</span>@if($aw->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from the old awards text">migrated</span>@endif</td>
                <td>@if($aw->source_url)<a href="{{ $aw->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($aw->evidence_file)<a href="{{ route('admin.quality.evidence', ['award', $aw->id]) }}" target="_blank">file</a>@endif @if(!$aw->source_url && !$aw->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $aw->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['award', $aw->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $aw->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['award', $aw->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openAward(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.awards.destroy', $aw->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this entry?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No award recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

{{-- Ratings & reviews --}}
@php $stars = fn ($v) => $v === null ? '-' : number_format($v, 1); @endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Ratings &amp; reviews <span class="text-muted fw-normal small">(averages are computed from the individual reviews, never typed)</span></span>
        <span>@can('quality-import')<a href="{{ route('admin.quality.reviews.import') }}" class="btn btn-sm btn-light border me-1"><i class="fas fa-file-csv me-1"></i> Import many</a>@endcan
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openReview()"><i class="fas fa-plus me-1"></i> Add review</button>@endif</span>
    </div>
    <div class="card-body pt-0">
        <div class="row g-3 align-items-center mb-2">
            <div class="col-auto">
                <div class="display-6 fw-bold {{ $ratings['average'] === null ? 'text-muted' : '' }}">{{ $stars($ratings['average']) }}<span class="fs-6 text-muted"> / 5</span></div>
                <div class="small text-muted">{{ number_format($ratings['count']) }} verified review(s)</div>
            </div>
            <div class="col">
                @foreach(\App\Models\Quality\EntityReview::SUB_RATINGS as $k => $label)
                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $label }} {{ $stars($ratings['subs'][$k]) }}</span>
                @endforeach
                <div class="small text-muted mt-1">
                    @if($ratings['below_count'] > 0)Includes {{ number_format($ratings['below_count']) }} review(s) recorded on the campuses, departments or programmes below. @endif
                    @if($ratings['unverified_count'] > 0)<span class="text-warning">{{ number_format($ratings['unverified_count']) }} unverified review(s) are not counted in the public figure.@if($ratingsAll['average'] !== null) Including them the average would be {{ $stars($ratingsAll['average']) }}.@endif</span>@endif
                    @if($ratings['count'] === 0 && $ratings['unverified_count'] === 0)No reviews recorded yet.@endif
                </div>
            </div>
        </div>
        @if(!empty($legacy) && ($legacy['rating'] ?? null) !== null)
            <div class="alert alert-light border small py-2 mb-2">
                <strong>Old typed value (unsourced):</strong> {{ $legacy['rating'] }}@if(!empty($legacy['rating_count'])) from {{ number_format($legacy['rating_count']) }} review(s)@endif.
                Nobody can check where this came from. It is replaced by the computed figure above as soon as verified reviews exist.
            </div>
        @endif
        @if($type === 'course' && ($roi || ($legacy['roi'] ?? null)))
            <div class="small mb-2">
                ROI:
                @if($roi)<span class="badge bg-info-subtle text-info">{{ $roi['label'] }}</span> <span class="text-muted">computed: total fee &divide; median salary = {{ $roi['payback_years'] }} years of salary ({{ $roi['academic_year'] }} placement figures)</span>
                @else<span class="text-muted">cannot be computed (needs the total fee and a verified median salary).</span>@endif
                @if($legacy['roi'] ?? null)<span class="text-muted"> Old typed value (unsourced): {{ $legacy['roi'] }}.</span>@endif
            </div>
        @endif
        @if($type === 'organisation')
            <div class="small mb-2">
                "Top" institution (computed from a verified NIRF Overall rank of {{ config('quality.top_nirf_overall_rank_max') }} or better):
                <span class="badge {{ $computedTop ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $computedTop ? 'Yes' : 'No' }}</span>
                @if(!empty($legacy['is_top']) && !$computedTop)<span class="text-warning"> The old hand-set "top" flag is on, but no verified ranking supports it.</span>@endif
            </div>
        @endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Reviewer</th><th>Rating</th><th>Review</th><th>Date</th><th>Published</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($reviews as $rv)
            @php $p = $rv->only(['id', 'reviewer_name', 'reviewer_type', 'rating', 'infrastructure', 'campus_life', 'academics', 'placements', 'value_for_money', 'title', 'comment', 'review_year', 'source', 'source_url', 'is_published', 'notes']) + ['reviewed_on' => $rv->reviewed_on?->toDateString()]; @endphp
            <tr class="{{ $rv->is_published ? '' : 'text-muted' }}">
                <td class="ps-4">{{ $rv->reviewer_name ?: 'Anonymous' }}<div class="small text-muted">{{ \App\Models\Quality\EntityReview::REVIEWER_TYPES[$rv->reviewer_type] }}</div></td>
                <td class="fw-bold">{{ number_format($rv->rating, 1) }}</td>
                <td style="max-width:340px"><div class="fw-bold small">{{ $rv->title }}</div><div class="small text-muted">{{ \Illuminate\Support\Str::limit($rv->comment, 140) }}</div></td>
                <td>{{ $rv->reviewed_on?->format('d M Y') ?? ($rv->review_year ?: '-') }}</td>
                <td>{!! $rv->is_published ? '<span class="badge bg-success-subtle text-success">Yes</span>' : '<span class="badge bg-secondary-subtle text-secondary">Hidden</span>' !!}</td>
                <td><span class="badge {{ $badge($rv->verification_status) }}">{{ ucfirst($rv->verification_status) }}</span>@if($rv->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $rv->legacy_source }}">migrated</span>@endif</td>
                <td>@if($rv->source_url)<a href="{{ $rv->source_url }}" target="_blank" rel="noopener">{{ $rv->source ?: 'link' }}</a>@elseif($rv->source){{ $rv->source }}@else<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $rv->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['review', $rv->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $rv->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['review', $rv->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openReview(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.reviews.destroy', $rv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this review?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No review recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

{{-- Placements --}}
@php $fmtInr = fn ($v) => \App\Services\Quality\Aggregates::money($v); @endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Placements <span class="text-muted fw-normal small">({{ $type === 'course' ? 'figures for this programme, per year' : 'totals computed from the verified programme figures below this ' . strtolower(\App\Support\Quality\Levels::LABELS[$type]) }})</span></span>
        <span>@can('quality-import')<a href="{{ route('admin.quality.placements.import') }}" class="btn btn-sm btn-light border me-1"><i class="fas fa-file-csv me-1"></i> Import many</a>@endcan
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openPlacement()"><i class="fas fa-plus me-1"></i> Add {{ $type === 'course' ? 'figures' : 'reported figures' }}</button>@endif</span>
    </div>
    @if($type !== 'course')
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="bg-light"><tr><th class="ps-4">Academic year</th><th class="text-end">Programmes with data</th><th class="text-end">Placed / graduating</th><th class="text-end">Placement %</th><th class="text-end">Highest salary</th><th class="text-end">Median salary*</th><th class="text-end pe-4">Higher studies</th></tr></thead>
            <tbody>
            @forelse($placementSummary['rows'] as $r)
                <tr>
                    <td class="ps-4 fw-bold">{{ $r['academic_year'] ?? 'unknown' }}</td>
                    <td class="text-end">{{ number_format($r['programmes']) }} <span class="text-muted small">of {{ number_format($placementSummary['programmes_total']) }}</span></td>
                    <td class="text-end">{{ $r['placed'] !== null ? number_format($r['placed']) . ' / ' . number_format($r['graduating']) : '-' }}</td>
                    <td class="text-end fw-bold">{{ $r['percentage'] !== null ? $r['percentage'] . '%' : '-' }}</td>
                    <td class="text-end">{{ $fmtInr($r['highest_salary']) }}</td>
                    <td class="text-end">{{ $fmtInr($r['median_salary']) }}</td>
                    <td class="text-end pe-4">{{ $r['higher_studies'] !== null ? number_format($r['higher_studies']) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No verified programme-level figures yet, so nothing can be computed here.@if($placementSummaryAll['rows']) {{ count($placementSummaryAll['rows']) }} year(s) of unverified programme figures exist.@endif</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-4 py-2 small text-muted">* Median of the programmes' reported medians, weighted by students placed (an average of medians, not a true median). Only verified programme-level rows are added up; figures reported at this level are shown separately below and never added in, so no student is counted twice.</div>
    @else
        @if($placements->isEmpty())<div class="px-4 pb-2 text-muted small">No placement figures recorded for this programme.</div>@endif
    @endif

    @if($type === 'course' || $placements->isNotEmpty())
    <div class="px-4 pt-2 small fw-bold">{{ $type === 'course' ? 'Recorded figures' : 'Reported by the institution at this level (not part of the totals above)' }}</div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Academic year</th><th>Placed / graduating</th><th>Placement %</th><th>Median</th><th>Highest</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @foreach($placements as $pl)
            @php $p = $pl->only(['id', 'academic_year', 'students_graduating', 'students_placed', 'students_higher_studies', 'median_salary', 'highest_salary', 'average_salary', 'recruiters_count', 'placement_source', 'source_url', 'notes']); $prob = $pl->problems(); @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $pl->academic_year ?: 'unknown' }}</td>
                <td>{{ $pl->students_placed !== null ? number_format($pl->students_placed) : '-' }} / {{ $pl->students_graduating !== null ? number_format($pl->students_graduating) : '-' }}@if($prob)<div class="small text-danger">{{ implode(' ', $prob) }}</div>@endif</td>
                <td>{{ $pl->placement_percentage !== null ? $pl->placement_percentage . '%' : '-' }}</td>
                <td>{{ $fmtInr($pl->median_salary) }}</td>
                <td>{{ $fmtInr($pl->highest_salary) }}</td>
                <td><span class="badge {{ $badge($pl->verification_status) }}">{{ ucfirst($pl->verification_status) }}</span>@if($pl->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $pl->legacy_source }}">migrated</span>@endif</td>
                <td>@if($pl->source_url)<a href="{{ $pl->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($pl->evidence_file)<a href="{{ route('admin.quality.evidence', ['placement', $pl->id]) }}" target="_blank">file</a>@endif @if(!$pl->source_url && !$pl->evidence_file)<span class="text-muted">{{ $pl->placement_source ?: 'none' }}</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $pl->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['placement', $pl->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $pl->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['placement', $pl->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openPlacement(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.placements.destroy', $pl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove these figures?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    @endif
</div>

{{-- Approvals --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Statutory approvals &amp; intake <span class="text-muted fw-normal small">(AICTE, NMC, BCI, PCI... one record per academic year)</span></span>
        @if($canEdit && $approvalBodies->isNotEmpty())<button class="btn btn-sm btn-primary" onclick="openApproval()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Body</th><th>Academic year</th><th>Approval no.</th><th>Sanctioned intake</th><th>Validity</th><th>Status</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($approvals as $ap)
            @php $p = $ap->only(['id', 'accreditation_body_id', 'academic_year', 'approval_number', 'sanctioned_intake', 'status', 'source_url', 'notes']) + ['valid_from' => $ap->valid_from?->toDateString(), 'valid_to' => $ap->valid_to?->toDateString()]; @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $ap->body->short_name }}<div class="small text-muted fw-normal">{{ \App\Models\Quality\AccreditationBody::KINDS[$ap->body->kind] }}</div></td>
                <td>{{ $ap->academic_year ?: '-' }}</td>
                <td>{{ $ap->approval_number ?: '-' }}</td>
                <td class="fw-bold">{{ $ap->sanctioned_intake !== null ? number_format($ap->sanctioned_intake) : '-' }}</td>
                <td>@if($ap->valid_from || $ap->valid_to){{ $ap->valid_from?->format('d M Y') ?? '?' }} &rarr; {{ $ap->valid_to?->format('d M Y') ?? 'open' }}@else<span class="text-muted">not recorded</span>@endif</td>
                <td><span class="badge {{ $ap->effective_status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ \App\Models\Quality\EntityApproval::STATUSES[$ap->effective_status] }}</span></td>
                <td><span class="badge {{ $badge($ap->verification_status) }}">{{ ucfirst($ap->verification_status) }}</span></td>
                <td>@if($ap->source_url)<a href="{{ $ap->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($ap->evidence_file)<a href="{{ route('admin.quality.evidence', ['approval', $ap->id]) }}" target="_blank">file</a>@endif @if(!$ap->source_url && !$ap->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $ap->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['approval', $ap->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $ap->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['approval', $ap->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openApproval(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.approvals.destroy', $ap->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this approval?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No statutory approval recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

{{-- Rankings --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Rankings</span>
        @if($canEdit)<button class="btn btn-sm btn-primary" onclick="openRank()"><i class="fas fa-plus me-1"></i> Add</button>@endif
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="bg-light"><tr><th class="ps-4">Ranking</th><th>Year</th><th>Rank</th><th>Verification</th><th>Source</th><th class="text-end pe-4">Actions</th></tr></thead>
        <tbody>
        @forelse($rankings as $r)
            @php $p = $r->only(['id', 'ranking_body_id', 'ranking_category_id', 'ranking_year', 'rank_exact', 'rank_band_from', 'rank_band_to', 'score', 'total_ranked', 'external_institute_id', 'source_url', 'notes']) + ['published_on' => $r->published_on?->toDateString()]; @endphp
            <tr>
                <td class="ps-4 fw-bold">{{ $r->body->short_name ?: $r->body->name }}<div class="small text-muted fw-normal">{{ $r->category->name }}@if($r->body->trust_level === 'low') · low-trust source @endif</div></td>
                <td>@if($r->ranking_year){{ $r->ranking_year }}@else<span class="badge bg-warning-subtle text-warning">unknown</span>@endif</td>
                <td class="fw-bold">{{ $r->rank_label }}@if($r->total_ranked) <span class="small text-muted fw-normal">of {{ $r->total_ranked }}</span>@endif @if($r->isBand())<span class="badge bg-light text-muted border">band</span>@endif</td>
                <td><span class="badge {{ $badge($r->verification_status) }}">{{ ucfirst($r->verification_status) }}</span>@if($r->legacy_source)<span class="badge bg-light text-muted border ms-1" title="Migrated from {{ $r->legacy_source }}">migrated</span>@endif</td>
                <td>@if($r->source_url)<a href="{{ $r->source_url }}" target="_blank" rel="noopener">link</a>@endif @if($r->evidence_file)<a href="{{ route('admin.quality.evidence', ['ranking', $r->id]) }}" target="_blank">file</a>@endif @if(!$r->source_url && !$r->evidence_file)<span class="text-muted">none</span>@endif</td>
                <td class="text-end pe-4 text-nowrap">
                    @if($canVerify && $r->verification_status !== 'verified')<form action="{{ route('admin.quality.records.verify', ['ranking', $r->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-success" title="Verify"><i class="fas fa-check"></i></button></form>@endif
                    @if($canVerify && $r->verification_status !== 'rejected')<form action="{{ route('admin.quality.records.reject', ['ranking', $r->id]) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light border text-warning" title="Reject"><i class="fas fa-ban"></i></button></form>@endif
                    @if($canEdit)
                        <button class="btn btn-sm btn-light border" data-row="{{ json_encode($p) }}" onclick="openRank(JSON.parse(this.dataset.row))"><i class="fas fa-edit text-primary"></i></button>
                        <form action="{{ route('admin.quality.rankings.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this ranking?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash-alt"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No ranking recorded at this level.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

@if($canEdit)
{{-- Award modal --}}
<div class="modal fade" id="awardModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="awardForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.awards.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="awardMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Award or recognition</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label fw-bold">Title <span class="text-danger">*</span></label><input name="title" class="form-control" maxlength="500" required placeholder="e.g. Best School of the Year"></div>
            <div class="col-md-5"><label class="form-label fw-bold">Type</label>
                <select name="kind" class="form-select">@foreach(\App\Models\Quality\EntityAward::KINDS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-bold">Given by (issuer)</label><input name="issuer" class="form-control" maxlength="255" placeholder="e.g. EducationWorld"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Year</label><input type="number" name="award_year" min="1900" max="{{ now()->year + 1 }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Category</label><input name="category" list="awardCats" class="form-control" maxlength="100"><datalist id="awardCats">@foreach(\App\Models\Quality\EntityAward::CATEGORIES as $c)<option value="{{ $c }}">@endforeach</datalist></div>
            <div class="col-md-4"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Description</label><textarea name="description" rows="2" class="form-control"></textarea></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">An award needs its year. To verify it you also need the issuer and a source link or evidence. Rankings, affiliations and accreditations are recorded in their own panels; use "Type" only for what really is an award or a membership.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Review modal --}}
<div class="modal fade" id="reviewModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="reviewForm" method="POST" action="{{ route('admin.quality.reviews.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="reviewMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold">Reviewer name</label><input name="reviewer_name" class="form-control" maxlength="150"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Reviewer is</label>
                <select name="reviewer_type" class="form-select">@foreach(\App\Models\Quality\EntityReview::REVIEWER_TYPES as $k => $l)<option value="{{ $k }}" @selected($k === 'other')>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label fw-bold">Rating (1-5) <span class="text-danger">*</span></label><input type="number" name="rating" min="1" max="5" step="0.5" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">Date</label><input type="date" name="reviewed_on" max="{{ now()->toDateString() }}" class="form-control"></div>
            @foreach(\App\Models\Quality\EntityReview::SUB_RATINGS as $k => $label)
                <div class="col-md-2 col-6"><label class="form-label small mb-1">{{ $label }}</label><input type="number" name="{{ $k }}" min="1" max="5" step="0.5" class="form-control form-control-sm" placeholder="1-5"></div>
            @endforeach
            <div class="col-md-2 col-6"><label class="form-label small mb-1">Year (if no date)</label><input type="number" name="review_year" min="1990" max="{{ now()->year }}" class="form-control form-control-sm"></div>
            <div class="col-12"><label class="form-label fw-bold">Title</label><input name="title" class="form-control" maxlength="255"></div>
            <div class="col-12"><label class="form-label fw-bold">Review</label><textarea name="comment" rows="3" class="form-control"></textarea></div>
            <div class="col-md-4"><label class="form-label fw-bold">Collected from</label><input name="source" class="form-control" maxlength="100" placeholder="e.g. Google, school website, survey"></div>
            <div class="col-md-5"><label class="form-label fw-bold">Link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-3 pt-4"><div class="form-check form-switch"><input type="hidden" name="is_published" value="0"><input class="form-check-input" type="checkbox" name="is_published" value="1" checked><label class="form-check-label">Published</label></div></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">The average is calculated from these rows. Unpublished and rejected reviews never count; only verified reviews feed the public figure. A review needs its source before it can be verified.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Placement modal --}}
<div class="modal fade" id="placementModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="placementForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.placements.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="placementMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Placement figures</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-3"><label class="form-label fw-bold">Academic year <span class="text-danger">*</span></label><input name="academic_year" class="form-control" placeholder="2024-25" pattern="\d{4}-\d{2}" maxlength="7" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">Students graduating</label><input type="number" name="students_graduating" min="0" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Students placed</label><input type="number" name="students_placed" min="0" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Higher studies</label><input type="number" name="students_higher_studies" min="0" class="form-control"></div>
            @foreach(['median_salary' => 'Median salary', 'highest_salary' => 'Highest salary', 'average_salary' => 'Average salary'] as $k => $label)
                <div class="col-md-3"><label class="form-label fw-bold">{{ $label }} (Rs / year)</label><input type="number" name="{{ $k }}" min="0" class="form-control salary" data-for="{{ $k }}"><div class="form-text lpa" id="lpa_{{ $k }}"></div></div>
            @endforeach
            <div class="col-md-3"><label class="form-label fw-bold">Recruiters</label><input type="number" name="recruiters_count" min="0" class="form-control"></div>
            <div class="col-md-5"><label class="form-label fw-bold">Where the figures come from</label><input name="placement_source" class="form-control" maxlength="255" placeholder="e.g. Placement report 2024-25"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-5"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">Enter the raw numbers only. The placement percentage and the department / campus / organisation totals are calculated. {{ $type === 'course' ? '' : 'Figures entered at this level are shown as "reported by the institution" and are not added into the totals.' }} Salaries are rupees per year.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Approval modal --}}
<div class="modal fade" id="approvalModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="approvalForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.approvals.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="approvalMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Statutory approval</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Approving body <span class="text-danger">*</span></label>
                <select name="accreditation_body_id" id="apBody" class="form-select" required><option value="">Select</option>
                    @foreach($approvalBodies as $b)<option value="{{ $b->id }}" data-validity="{{ (int) $b->has_validity }}">{{ $b->short_name }} - {{ $b->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-bold">Academic year <span class="text-danger">*</span></label>
                <input name="academic_year" list="ayList" class="form-control" placeholder="2025-26" pattern="\d{4}-\d{2}" maxlength="7" required>
                <datalist id="ayList">@for($y = now()->year + 1; $y >= now()->year - 4; $y--)<option value="{{ $y }}-{{ substr($y + 1, -2) }}">@endfor</datalist></div>
            <div class="col-md-3"><label class="form-label fw-bold">Sanctioned intake</label><input type="number" name="sanctioned_intake" min="0" class="form-control" placeholder="seats"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Approval / letter number</label><input name="approval_number" class="form-control" maxlength="100"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid from</label><input type="date" name="valid_from" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid to</label><input type="date" name="valid_to" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">@foreach(\App\Models\Quality\EntityApproval::STATUSES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-4"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">One record per body per academic year, so intake can be tracked year by year. Changing a verified record sends it back to unverified.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Accreditation modal --}}
<div class="modal fade" id="accModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="accForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.accreditations.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="accMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Accreditation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-bold">Body <span class="text-danger">*</span></label>
                <select name="accreditation_body_id" id="accBody" class="form-select" required><option value="">Select</option>
                    @foreach($accBodies as $b)<option value="{{ $b->id }}" data-grade="{{ (int) $b->has_grade }}" data-options="{{ json_encode($b->grade_options ?? []) }}" data-validity="{{ (int) $b->has_validity }}">{{ $b->short_name }} - {{ $b->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-bold">Grade</label>
                <input name="grade" id="accGrade" list="gradeList" class="form-control" maxlength="30"><datalist id="gradeList"></datalist></div>
            <div class="col-md-3"><label class="form-label fw-bold">Score / CGPA</label><input type="number" step="0.01" name="score" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Cycle</label><input type="number" name="cycle" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid from</label><input type="date" name="valid_from" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Valid to</label><input type="date" name="valid_to" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">@foreach(\App\Models\Quality\EntityAccreditation::STATUSES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-bold">Certificate / approval number</label><input name="certificate_number" class="form-control" maxlength="100"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">Changing a verified record sends it back to unverified.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Ranking modal --}}
<div class="modal fade" id="rankModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="rankForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.quality.rankings.store', [$type, $entity->getKey()]) }}">
        @csrf <span id="rankMethod"></span>
        <div class="modal-header"><h5 class="modal-title">Ranking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold">Ranking body <span class="text-danger">*</span></label>
                <select name="ranking_body_id" id="rankBody" class="form-select" required><option value="">Select</option>
                    @foreach($rankingBodies as $b)<option value="{{ $b->id }}">{{ $b->short_name ?: $b->name }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                <select name="ranking_category_id" id="rankCat" class="form-select" required><option value="">Select body first</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" data-body="{{ $c->ranking_body_id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label fw-bold">Year <span class="text-danger">*</span></label><input type="number" name="ranking_year" min="1990" max="{{ now()->year + 1 }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label fw-bold">Exact rank</label><input type="number" name="rank_exact" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">...or band from</label><input type="number" name="rank_band_from" min="1" class="form-control" placeholder="101"></div>
            <div class="col-md-3"><label class="form-label fw-bold">band to</label><input type="number" name="rank_band_to" min="1" class="form-control" placeholder="150"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Score</label><input type="number" step="0.001" name="score" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Total ranked</label><input type="number" name="total_ranked" min="1" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Body's institute ID</label><input name="external_institute_id" class="form-control" maxlength="40" placeholder="e.g. IR-E-U-0001"></div>
            <div class="col-md-3"><label class="form-label fw-bold">Published on</label><input type="date" name="published_on" class="form-control"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Source link</label><input type="url" name="source_url" class="form-control" placeholder="https://"></div>
            <div class="col-md-6"><label class="form-label fw-bold">Evidence (pdf / image)</label><input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
            <div class="col-12"><label class="form-label fw-bold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="small text-muted mt-3">Give an exact rank <em>or</em> a band (many results are published as 101-150). Never enter a made-up number for a band.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endif
@endsection

@push('scripts')
<script>
    const accStore = @json(route('admin.quality.accreditations.store', [$type, $entity->getKey()]));
    const accBase = @json(url('admin/quality/accreditations'));
    const approvalStore = @json(route('admin.quality.approvals.store', [$type, $entity->getKey()]));
    const approvalBase = @json(url('admin/quality/approvals'));
    const rankStore = @json(route('admin.quality.rankings.store', [$type, $entity->getKey()]));
    const rankBase = @json(url('admin/quality/rankings'));

    function fillForm(form, row) {
        Object.entries(row).forEach(([name, val]) => {
            if (name === 'id') return;
            const el = form.querySelector(`[name="${name}"]`);
            if (el) el.value = val ?? '';
        });
    }
    function syncAccBody() {
        const opt = document.querySelector('#accBody option:checked');
        const list = document.getElementById('gradeList');
        list.innerHTML = '';
        if (!opt || !opt.value) return;
        document.getElementById('accGrade').disabled = opt.dataset.grade !== '1';
        JSON.parse(opt.dataset.options || '[]').forEach(g => list.insertAdjacentHTML('beforeend', `<option value="${g}">`));
    }
    function syncRankCats() {
        const body = document.getElementById('rankBody').value;
        const sel = document.getElementById('rankCat');
        Array.from(sel.options).forEach(o => { if (o.value) { o.hidden = o.disabled = o.dataset.body !== body; } });
        if (sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
    }
    const reviewStore = @json(route('admin.quality.reviews.store', [$type, $entity->getKey()]));
    const reviewBase = @json(url('admin/quality/reviews'));
    const placementStore = @json(route('admin.quality.placements.store', [$type, $entity->getKey()]));
    const placementBase = @json(url('admin/quality/placements'));
    function lpa(el) {
        const out = document.getElementById('lpa_' + el.dataset.for);
        const v = parseInt(el.value, 10);
        out.textContent = v >= 100000 ? '= ' + (v / 100000).toFixed(2).replace(/\.?0+$/, '') + ' LPA' : '';
    }
    document.querySelectorAll('.salary').forEach(el => el.addEventListener('input', () => lpa(el)));
    const awardStore = @json(route('admin.quality.awards.store', [$type, $entity->getKey()]));
    const awardBase = @json(url('admin/quality/awards'));
    function openAward(row) {
        const form = document.getElementById('awardForm');
        form.reset();
        form.action = row ? awardBase + '/' + row.id : awardStore;
        document.getElementById('awardMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        new bootstrap.Modal(document.getElementById('awardModal')).show();
    }
    function openReview(row) {
        const form = document.getElementById('reviewForm');
        form.reset();
        form.action = row ? reviewBase + '/' + row.id : reviewStore;
        document.getElementById('reviewMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) {
            fillForm(form, row);
            form.querySelector('input[type=checkbox][name=is_published]').checked = !!row.is_published;
        }
        new bootstrap.Modal(document.getElementById('reviewModal')).show();
    }
    function openPlacement(row) {
        const form = document.getElementById('placementForm');
        form.reset();
        form.action = row ? placementBase + '/' + row.id : placementStore;
        document.getElementById('placementMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        form.querySelectorAll('.salary').forEach(lpa);
        new bootstrap.Modal(document.getElementById('placementModal')).show();
    }
    function openApproval(row) {
        const form = document.getElementById('approvalForm');
        form.reset();
        form.action = row ? approvalBase + '/' + row.id : approvalStore;
        document.getElementById('approvalMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        new bootstrap.Modal(document.getElementById('approvalModal')).show();
    }
    function openAcc(row) {
        const form = document.getElementById('accForm');
        form.reset();
        form.action = row ? accBase + '/' + row.id : accStore;
        document.getElementById('accMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        syncAccBody();
        new bootstrap.Modal(document.getElementById('accModal')).show();
    }
    function openRank(row) {
        const form = document.getElementById('rankForm');
        form.reset();
        form.action = row ? rankBase + '/' + row.id : rankStore;
        document.getElementById('rankMethod').innerHTML = row ? '<input type="hidden" name="_method" value="PUT">' : '';
        if (row) fillForm(form, row);
        syncRankCats();
        if (row) document.getElementById('rankCat').value = row.ranking_category_id;
        new bootstrap.Modal(document.getElementById('rankModal')).show();
    }
    document.getElementById('accBody')?.addEventListener('change', syncAccBody);
    document.getElementById('rankBody')?.addEventListener('change', syncRankCats);
</script>
@endpush
