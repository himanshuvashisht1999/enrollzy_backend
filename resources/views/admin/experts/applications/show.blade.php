@extends('admin.layouts.master')

@section('title', 'Application: ' . $app->name)

@php
    use App\Models\ExpertApplication as A;
    use App\Models\ExpertDocument as D;
    $badge = fn ($s) => match ($s) {
        'pending' => 'bg-warning-subtle text-warning-emphasis', 'under_review' => 'bg-info-subtle text-info-emphasis',
        'needs_changes', 'rejected' => 'bg-danger-subtle text-danger', 'approved', 'verified' => 'bg-success-subtle text-success',
        default => 'bg-secondary-subtle text-secondary',
    };
    $list = fn ($v) => $v ? implode(', ', (array) $v) : null;
    $rows = array_filter([
        'Mobile' => $app->mobile, 'Email' => $app->email, 'Gender' => $app->gender ? ucfirst($app->gender) : null,
        'City' => collect([$app->city, $app->state])->filter()->implode(', '),
        'Designation' => $app->designation, 'Organisation' => $app->organisation, 'Highest qualification' => $app->highest_qualification,
        'Experience' => $app->years_of_experience !== null ? $app->years_of_experience . ' years' : null, 'Students guided' => $app->students_counselled,
        'Expertise' => $list($app->expertise_areas), 'Classes / levels' => $list($app->levels), 'Languages' => $list($app->languages),
        'Sessions' => $list($app->session_modes), 'Proposed fee' => $app->proposed_fee ? '₹' . number_format($app->proposed_fee) . ' for 30 minutes' : null,
    ], fn ($v) => $v !== null && $v !== '');
    $pendingDocs = $app->documents->where('status', 'pending')->count();
    $rejectedDocs = $app->documents->where('status', 'rejected')->count();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div class="d-flex gap-3 align-items-center">
        @if($app->photo)<img src="{{ route('expert-applications.photo', $app) }}" alt="" width="64" height="64" class="rounded-circle border" style="object-fit:cover">@endif
        <div>
            <h4 class="mb-1 fw-bold">{{ $app->name }}</h4>
            <span class="badge {{ $badge($app->status) }}">{{ A::STATUSES[$app->status] ?? $app->status }}</span>
            <span class="text-muted small ms-2">Application {{ $app->attempt_no }} of {{ $eligibility['max'] }} · sent {{ $app->submitted_at?->format('d M Y, h:i A') ?? '-' }}</span>
            @if($app->decided_at)<span class="text-muted small ms-2">· decided {{ $app->decided_at->format('d M Y') }}{{ $app->decider ? ' by ' . $app->decider->name : '' }}</span>@endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expert-applications.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Applications</a>
        @if($app->expert)<a href="{{ route('experts.edit', $app->expert) }}" class="btn btn-primary"><i class="fas fa-user-tie me-1"></i> Expert profile</a>@endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        {{-- the form --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Application</h6>
            <table class="table table-sm mb-3"><tbody>
                @foreach($rows as $k => $v)<tr><th class="text-muted fw-normal" style="width:38%">{{ $k }}</th><td>{{ $v }}</td></tr>@endforeach
                @if($app->linkedin_url)<tr><th class="text-muted fw-normal">LinkedIn</th><td><a href="{{ $app->linkedin_url }}" target="_blank" rel="noopener noreferrer">{{ Str::limit($app->linkedin_url, 60) }}</a></td></tr>@endif
                @if($app->website_url)<tr><th class="text-muted fw-normal">Website</th><td><a href="{{ $app->website_url }}" target="_blank" rel="noopener noreferrer">{{ Str::limit($app->website_url, 60) }}</a></td></tr>@endif
            </tbody></table>
            @if($app->short_bio)<div class="mb-2"><div class="small text-muted">Short bio</div>{{ $app->short_bio }}</div>@endif
            @if($app->detailed_bio)<div><div class="small text-muted">About</div><div style="white-space:pre-line">{{ $app->detailed_bio }}</div></div>@endif

            {{-- the form's optional profile sections: copied to the expert's profile on approval --}}
            @php
                $profile = \App\Support\Experts\Applications::profileOf($app);
                $link = fn ($u) => preg_match('~^https?://~i', (string) $u) ? '<a href="' . e($u) . '" target="_blank" rel="noopener noreferrer">' . e(Str::limit($u, 60)) . '</a>' : e($u);
            @endphp
            @if($profile)
                <h6 class="fw-bold mt-4 mb-2">Profile details <span class="text-muted fw-normal small">(filled on the expert's profile when approved)</span></h6>
                <table class="table table-sm mb-0"><tbody>
                @foreach(\App\Support\Experts\Applications::PROFILE_KEYS as $k => $label)
                    @continue(!isset($profile[$k]))
                    @php $v = $profile[$k]; @endphp
                    <tr><th class="text-muted fw-normal" style="width:38%">{{ $label }}</th><td>
                        @if($k === 'date_of_birth'){{ \Carbon\Carbon::parse($v)->format('j M Y') }}
                        @elseif($k === 'top_rank_students')
                            @foreach((array) $v as $r)<div>{{ implode(' · ', array_filter([$r['name'] ?? '', $r['rank'] ?? '', $r['exam'] ?? '', $r['year'] ?? ''])) }}</div>@endforeach
                        @elseif($k === 'articles_written')
                            @foreach((array) $v as $a)<div>{{ ($a['title'] ?? '') ?: 'Article' }}: {!! $link($a['url'] ?? '') !!}</div>@endforeach
                        @elseif($k === 'demo_lecture_videos')
                            @foreach((array) $v as $u)<div>{!! $link($u) !!}</div>@endforeach
                        @elseif(is_array($v)){{ implode(', ', $v) }}
                        @elseif(str_ends_with($k, '_url')){!! $link($v) !!}
                        @else<span style="white-space:pre-line">{{ $v }}</span>@endif
                    </td></tr>
                @endforeach
                </tbody></table>
            @endif
        </div></div>

        {{-- documents --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-1">Documents <span class="text-muted fw-normal small">{{ $app->documents->count() }} uploaded{{ $pendingDocs ? ', ' . $pendingDocs . ' to check' : '' }}</span></h6>
            <p class="small text-muted">Open each file, then mark it verified or rejected. A rejected document needs a reason: the applicant sees it and can upload a better one while the application is open.</p>
            @forelse($app->documents as $d)
                @php $tone = ['verified' => 'success', 'rejected' => 'danger'][$d->status] ?? 'warning'; @endphp
                {{-- the card takes its status colour: amber pending, green verified, red rejected --}}
                <div class="rounded-3 p-3 mb-3 shadow-sm" style="border: 1px solid var(--bs-{{ $tone }}-border-subtle); border-left: 5px solid var(--bs-{{ $tone }}); background: #fff;">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="badge bg-light text-dark border">{{ D::TYPES[$d->type] ?? $d->type }}</span>
                            <span class="fw-semibold ms-1">{{ $d->title }}</span>
                            <div class="small text-muted">{{ collect([$d->issued_by, $d->year])->filter()->implode(', ') }}{{ $d->size ? ' · ' . number_format($d->size / 1024) . ' KB' : '' }}</div>
                        </div>
                        <div class="text-nowrap">
                            <span class="badge {{ $badge($d->status) }}">{{ D::STATUSES[$d->status] }}</span>
                            <a href="{{ route('expert-documents.file', $d) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border ms-1"><i class="fas fa-external-link-alt me-1"></i>Open</a>
                        </div>
                    </div>
                    <form action="{{ route('expert-documents.review', $d) }}" method="POST" class="row g-2 align-items-center mt-2">
                        @csrf
                        <div class="col-md-3"><select name="status" class="form-select form-select-sm">@foreach(D::STATUSES as $k => $v)<option value="{{ $k }}" @selected($d->status === $k)>{{ $v }}</option>@endforeach</select></div>
                        <div class="col-md-5"><input type="text" name="review_note" value="{{ $d->review_note }}" maxlength="500" class="form-control form-control-sm" placeholder="Note (required to reject: what is wrong)"></div>
                        <div class="col-md-2">@if($d->type !== 'id_proof')<div class="form-check small"><input class="form-check-input" type="checkbox" name="show_on_profile" value="1" id="sp{{ $d->id }}" @checked($d->show_on_profile)><label class="form-check-label" for="sp{{ $d->id }}">On profile</label></div>@else<span class="small text-muted">Never public</span>@endif</div>
                        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Save</button></div>
                    </form>
                    @if($d->reviewed_at)<div class="small text-muted mt-1">Checked {{ $d->reviewed_at->format('d M Y') }}{{ $d->reviewer ? ' by ' . $d->reviewer->name : '' }}</div>@endif
                </div>
            @empty
                <p class="text-muted small mb-0">No documents uploaded.</p>
            @endforelse
        </div></div>

        @if($others->count())
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-2">Earlier applications by this person</h6>
            @foreach($others as $o)
                <div class="d-flex justify-content-between border-top py-2 small">
                    <span>Application {{ $o->attempt_no }} · {{ $o->submitted_at?->format('d M Y') }} <span class="badge {{ $badge($o->status) }} ms-1">{{ A::STATUSES[$o->status] }}</span></span>
                    <a href="{{ route('expert-applications.show', $o) }}">Open</a>
                </div>
            @endforeach
        </div></div>
        @endif
    </div>

    <div class="col-lg-5">
        {{-- decide --}}
        @if(in_array($app->status, A::REVIEW, true))
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Decide</h6>
            @if($pendingDocs || $rejectedDocs)<div class="alert alert-warning small py-2">{{ $pendingDocs ? $pendingDocs . ' document(s) not checked yet. ' : '' }}{{ $rejectedDocs ? $rejectedDocs . ' rejected.' : '' }}</div>@endif
            <form action="{{ route('expert-applications.status', $app) }}" method="POST" class="mb-3">
                @csrf
                <label class="form-label small fw-bold">Set status</label>
                <select name="status" class="form-select form-select-sm mb-2">
                    @foreach(['pending' => 'Pending review', 'under_review' => 'Under review', 'needs_changes' => 'Changes needed (applicant edits and sends again)', 'rejected' => 'Not approved (closes the application)'] as $k => $v)
                        <option value="{{ $k }}" @selected($app->status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="3" maxlength="3000" class="form-control form-control-sm mb-2" placeholder="Message to the applicant: what to fix, or why it is not approved (required for those two)">{{ old('message') }}</textarea>
                <button class="btn btn-sm btn-outline-primary" data-confirm="Change the status? The applicant sees it and the message.">Save status</button>
            </form>
            <hr>
            <form action="{{ route('expert-applications.approve', $app) }}" method="POST">
                @csrf
                <label class="form-label small fw-bold">Approve as</label>
                <select name="expert_category_id" class="form-select form-select-sm mb-2" required><option value="">Expert category</option>@foreach($categories as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                <textarea name="message" rows="2" maxlength="3000" class="form-control form-control-sm mb-2" placeholder="Welcome message (optional)"></textarea>
                <button class="btn btn-sm btn-success" data-confirm="Approve {{ $app->name }}? This creates their expert profile (Private until you make it Public) and opens the expert dashboard for them."><i class="fas fa-check me-1"></i> Approve</button>
                <div class="small text-muted mt-1">If an expert already has this mobile number, that profile is linked instead of making a new one.</div>
            </form>
        </div></div>
        @elseif($app->status === 'rejected')
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
            <h6 class="fw-bold mb-2">Applying again</h6>
            @php $e = $eligibility; @endphp
            <p class="small mb-2">
                @if($e['can']) They can start a new application now.
                @elseif($e['reason'] === 'wait') They can apply again from {{ $e['from']->format('d M Y') }} (application {{ $e['used'] + 1 }} of {{ $e['max'] }}).
                @elseif($e['reason'] === 'limit') They have used all {{ $e['max'] }} applications.
                @elseif($e['reason'] === 'open') They have a newer application open.
                @else They are an expert now. @endif
            </p>
            @if(!$e['can'] && in_array($e['reason'], ['wait', 'limit'], true) && $e['latest']?->id === $app->id)
            <form action="{{ route('expert-applications.allow-reapply', $app) }}" method="POST">@csrf
                <button class="btn btn-sm btn-outline-primary" data-confirm="Let {{ $app->name }} apply again now?">Allow another application now</button></form>
            @endif
        </div></div>
        @endif

        {{-- thread --}}
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
            <h6 class="fw-bold mb-3">Messages and history</h6>
            <div class="mb-3" style="max-height:480px;overflow:auto">
            @forelse($app->notes as $n)
                <div class="mb-2 p-2 rounded-3 small {{ $n->internal ? 'bg-warning-subtle' : ($n->author === 'applicant' ? 'bg-light border' : 'bg-primary-subtle') }}">
                    <div class="d-flex justify-content-between text-muted">
                        <span>{{ $n->author === 'applicant' ? $app->name : ($n->author === 'system' ? 'System' : ($n->admin->name ?? 'Staff')) }}{{ $n->internal ? ' · staff only' : '' }}</span>
                        <span>{{ $n->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($n->to_status)<div class="fw-semibold">Status: {{ A::STATUSES[$n->from_status] ?? $n->from_status }} → {{ A::STATUSES[$n->to_status] ?? $n->to_status }}</div>@endif
                    @if($n->body)<div style="white-space:pre-line">{{ $n->body }}</div>@endif
                </div>
            @empty
                <p class="text-muted small">Nothing yet.</p>
            @endforelse
            </div>
            <form action="{{ route('expert-applications.note', $app) }}" method="POST">
                @csrf
                <textarea name="body" rows="3" maxlength="3000" class="form-control form-control-sm mb-2" placeholder="Write to the applicant, or a note for staff" required></textarea>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check small"><input class="form-check-input" type="checkbox" name="internal" value="1" id="internal"><label class="form-check-label" for="internal">Staff only (the applicant does not see it)</label></div>
                    <button class="btn btn-sm btn-primary">Send</button>
                </div>
            </form>
        </div></div>
    </div>
</div>
@endsection
