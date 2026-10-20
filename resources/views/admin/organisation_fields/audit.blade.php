@extends('admin.layouts.master')

@section('title', 'Organisation fields: audit')

@php
    $levelNames = ['organisation' => 'Organisation', 'campus' => 'Campus', 'department' => 'Department', 'course' => 'Programme'];
    $badge = ['no_column' => 'bg-danger-subtle text-danger', 'not_on_form' => 'bg-warning-subtle text-warning', 'form_unknown' => 'bg-secondary-subtle text-secondary', 'managed_elsewhere' => 'bg-info-subtle text-info'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Organisation fields: audit</h4>
        <p class="text-muted mb-0">Does the field catalogue match the database and the edit forms staff really use? The AI updater may only change a field that passes every test, so a failing field below is one the AI cannot touch.</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.organisation-fields.rescan') }}">@csrf<button class="btn btn-light border"><i class="fas fa-sync me-1"></i> Re-read the forms</button></form>
        <a href="{{ route('admin.organisation-fields.index') }}" class="btn btn-primary">Organisation Fields</a>
    </div>
</div>

<div class="alert alert-light border small">
    Forms last read <strong>{{ $manifest['generated_at'] ?? 'never' }}</strong>. This happens automatically whenever an edit template changes. Each form is read by opening the real edit page for a sample record of that organisation type.
    @if(!empty($manifest['errors']))<div class="text-danger mt-1">Could not read: {{ implode(' | ', $manifest['errors']) }}</div>@endif
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body small">
    <strong>"Managed elsewhere"</strong> (ranking, accreditation, approval, award, rating and placement values that are now kept as records) is
    <span class="badge {{ $quality['on'] ? 'bg-success' : 'bg-secondary' }}">{{ $quality['on'] ? 'ON: the AI cannot write these old columns' : 'OFF: the AI can still write these old columns' }}</span>.
    Change <code>block_quality_managed</code> in <code>config/organisation_fields.php</code>.
</div></div>

@php $reasonText = ['records' => 'Kept as records under Rankings & Accreditation', 'identity' => 'Set on the Identity page (link to the real organisation)', 'master' => 'Picked from a master list on the form; the AI cannot choose master ids', 'no_column' => 'No database column yet']; @endphp
@if(collect($unavailable)->flatten()->count() || collect($renames)->flatten()->count())
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body small">
    <div class="fw-bold mb-2">Catalogue fields not offered</div>
    <p class="text-muted mb-2">These names were in the field catalogue but nothing could ever be saved under them. They are hidden from the Organisation Fields screen and the AI updater. To bring one back, add the column and an input on the edit form, then remove its line from <code>unavailable</code> in <code>config/organisation_fields.php</code>.</p>
    @foreach(collect($unavailable)->flatMap(fn ($fields, $level) => collect($fields)->map(fn ($r, $n) => ['level' => $level, 'name' => $n, 'reason' => $r]))->groupBy('reason') as $reason => $items)
        <div class="mb-2"><span class="badge bg-secondary-subtle text-secondary">{{ $items->count() }}</span> <strong>{{ $reasonText[$reason] ?? $reason }}:</strong> <span class="text-muted">{{ $items->map(fn ($i) => $i['level'] . '.' . $i['name'])->implode(', ') }}</span></div>
    @endforeach
    @if(collect($renames)->flatten()->count())
        <div class="mt-2"><strong>Pointed at the real column</strong> (a tick saved under the old name still counts): <span class="text-muted">@foreach($renames as $level => $map)@foreach($map as $old => $new){{ $level }}.{{ $old }} &rarr; {{ $new }}@if(!$loop->last), @endif @endforeach @endforeach</span></div>
    @endif
</div></div>
@endif

@foreach($types as $typeId => $title)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 fw-bold">{{ $title }} <span class="text-muted fw-normal small">(type {{ $typeId }})</span></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="bg-light"><tr><th class="ps-4">Level</th><th class="text-end">In catalogue</th><th class="text-end text-success">Usable</th><th class="text-end">No column</th><th class="text-end">Not on form</th><th class="text-end">Managed elsewhere</th><th>Edit form</th></tr></thead>
            <tbody>
            @foreach($levelNames as $level => $name)
                @php $r = $report[$typeId][$level]; $s = $r['summary']; @endphp
                <tr>
                    <td class="ps-4 fw-bold">{{ $name }}</td>
                    <td class="text-end">{{ $s['total'] }}</td>
                    <td class="text-end text-success fw-bold">{{ $s['ok'] }}</td>
                    <td class="text-end {{ $s['no_column'] ? 'text-danger fw-bold' : 'text-muted' }}">{{ $s['no_column'] }}</td>
                    <td class="text-end {{ $s['not_on_form'] ? 'text-warning fw-bold' : 'text-muted' }}">{{ $s['not_on_form'] }}</td>
                    <td class="text-end text-muted">{{ $s['managed_elsewhere'] }}</td>
                    <td class="small text-muted">@if($r['form']){{ count($r['form']['fields']) }} fields ({{ $r['form']['source'] }})@else <span class="text-danger">not read</span>@endif</td>
                </tr>
                @if(count($r['problems']))
                    <tr><td></td><td colspan="6" class="pb-3">
                        <details><summary class="small text-primary" style="cursor:pointer">Show the {{ count($r['problems']) }} field(s) the AI cannot use</summary>
                            <div class="mt-2">
                            @foreach($r['problems'] as $field => $st)
                                <span class="badge {{ $badge[$st['state']] ?? 'bg-light text-dark' }} me-1 mb-1" title="{{ $st['reason'] }}">{{ $field }}</span>
                            @endforeach
                            <div class="small text-muted mt-1">Red: no database column, so it can never be saved. Amber: a column exists but the edit form has no input for it, so staff could not see or correct it. Fix by adding a column or a form input, or remove it from the catalogue.</div>
                            </div>
                        </details>
                    </td></tr>
                @endif
            @endforeach
            </tbody>
        </table></div>
    </div>
@endforeach
@endsection
