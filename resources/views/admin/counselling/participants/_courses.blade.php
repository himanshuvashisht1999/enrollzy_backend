{{-- One institute's courses in a cycle (loaded into the courses window of participants/index): each course once, with its
     fee range and its quotas; click a quota to edit that row, × to remove it. --}}
@php
    $types = \App\Support\Counselling\CnsBlocks::INSTITUTE_TYPES;
    $inr = function ($n) { $n = (string) round((float) $n); $last = substr($n, -3); $rest = substr($n, 0, -3); return '₹' . ($rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' : '') . $last; };
    $fee = fn ($min, $max) => $min === null ? '-' : ($inr($min) . ($max !== null && $max != $min ? ' – ' . $inr($max) : ''));
@endphp
{{-- add a quota to every course of this institute at once (courses that already have it are skipped) --}}
@can('cns-counsellings-edit')
<form method="POST" action="{{ route('admin.counselling.participants.bulk', $cycle) }}" class="d-flex align-items-center gap-2 flex-wrap mb-3 p-2 rounded-3 border bg-light" onsubmit="this.querySelector('button').disabled = true">
    @csrf
    <input type="hidden" name="organisation_id" value="{{ $organisation }}">
    @foreach($courseIds as $cid)<input type="hidden" name="course_ids[]" value="{{ $cid }}">@endforeach
    <span class="small fw-semibold">Add a quota to all {{ $courses->count() }} {{ \Illuminate\Support\Str::plural('course', $courses->count()) }}:</span>
    <select name="quota_ids[]" class="form-select form-select-sm" style="width:auto" required>
        <option value="">Choose quota</option>
        @foreach($quotas as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach
    </select>
    <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add</button>
</form>
@endcan
<div class="d-flex justify-content-between align-items-center gap-2 mb-2 flex-wrap">
    <span class="small text-muted">{{ $courses->count() }} {{ \Illuminate\Support\Str::plural('course', $courses->count()) }}. Click a quota to edit it, × to remove it, + to add one.</span>
    @if($courses->count() > 8)<input type="search" class="form-control form-control-sm" placeholder="Filter courses" style="width:220px" oninput="const q = this.value.toLowerCase(); this.closest('.pc-courses').querySelectorAll('tbody tr').forEach(tr => tr.classList.toggle('d-none', q !== '' && !tr.dataset.t.includes(q)))">@endif
</div>
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="bg-light"><tr><th style="width:40px">#</th><th>Course</th><th class="text-nowrap">Tuition / yr</th><th>Quotas</th><th class="text-end">Seats</th></tr></thead>
        <tbody>
        @foreach($courses as $i => $c)
            <tr data-t="{{ mb_strtolower($c['name']) }}">
                <td class="text-muted">{{ $i + 1 }}</td>
                <td class="fw-semibold">{{ $c['name'] }}</td>
                <td class="text-nowrap">{{ $fee($c['fee_min'], $c['fee_max']) }}</td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                    @foreach($c['rows'] as $p)
                        @php $payload = ['id' => $p->id, 'organisation_id' => $p->organisation_id, 'org_name' => $college, 'course_id' => $p->course_id, 'course_name' => $c['name'],
                            'quota_id' => $p->quota_id, 'institute_type' => $p->institute_type, 'institute_code' => $p->institute_code, 'annual_tuition_fee' => $p->annual_tuition_fee,
                            'other_fees' => $p->other_fees, 'bond_details' => $p->bond_details, 'total_seats' => $p->total_seats];
                            $tip = implode(' · ', array_filter([$types[$p->institute_type] ?? null, $p->institute_code ? 'Code ' . $p->institute_code : null, $p->annual_tuition_fee !== null ? $inr($p->annual_tuition_fee) . '/yr' : null, $p->total_seats !== null ? $p->total_seats . ' seats' : null])); @endphp
                        <span class="badge rounded-pill bg-light text-dark border d-inline-flex align-items-center gap-1 fw-normal" style="font-size:.8rem">
                            @can('cns-counsellings-edit')
                                <a href="#" class="text-decoration-none text-dark" title="Edit: {{ $tip ?: 'no details yet' }}" data-row="{{ json_encode($payload) }}" onclick="event.preventDefault(); bootstrap.Modal.getInstance(document.getElementById('coursesModal'))?.hide(); openParticipant(JSON.parse(this.dataset.row))">{{ $p->quota->name ?? 'Quota' }}</a>
                                <form action="{{ route('admin.counselling.participants.destroy', $p) }}" method="POST" class="d-inline" data-confirm="Remove {{ $c['name'] }} ({{ $p->quota->name ?? 'quota' }}) from this cycle?">@csrf @method('DELETE')<button class="btn btn-link p-0 text-danger text-decoration-none lh-1" title="Remove this quota row">&times;</button></form>
                            @else
                                <span title="{{ $tip }}">{{ $p->quota->name ?? 'Quota' }}</span>
                            @endcan
                        </span>
                    @endforeach
                    {{-- + add a quota to this course (only the quotas it does not have yet) --}}
                    @can('cns-counsellings-edit')
                        @php $missing = $quotas->whereNotIn('id', $c['rows']->pluck('quota_id')); @endphp
                        @if($missing->isNotEmpty())
                            <form method="POST" action="{{ route('admin.counselling.participants.bulk', $cycle) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="organisation_id" value="{{ $organisation }}">
                                <input type="hidden" name="course_ids[]" value="{{ $c['id'] }}">
                                <select name="quota_ids[]" class="form-select form-select-sm py-0 border-primary text-primary" style="width:auto; font-size:.8rem; height:24px" title="Add a quota to {{ $c['name'] }}"
                                    onchange="if (this.value) { this.form.submit(); this.style.opacity = .5; }">
                                    <option value="">+ Add quota</option>
                                    @foreach($missing as $q)<option value="{{ $q->id }}">{{ $q->name }}</option>@endforeach
                                </select>
                            </form>
                        @endif
                    @endcan
                    </div>
                </td>
                <td class="text-end">{{ $c['seats'] ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
