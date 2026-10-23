{{-- Round settings fields. Needs: $round (new or existing), $roundTypes, $quotas, $dialog (which form this is). Blank override = inherit. --}}
@php
    $tri = fn ($v) => $v === null ? '' : ($v ? '1' : '0');
    // Typed values come back only into the form that failed, never into another form on the same page.
    $mine = old('_dialog') === $dialog;
    $old = fn ($k, $default = null) => $mine ? old($k, $default) : $default;
@endphp
<input type="hidden" name="_dialog" value="{{ $dialog }}">
<div class="row g-3">
    <div class="col-md-6"><label class="form-label fw-bold">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ $old('name', $round->name) }}" placeholder="Round 1, Mop-up Round" required></div>
    <div class="col-md-4"><label class="form-label fw-bold">Round Type *</label>
        <select name="round_type_id" class="form-select" required>
            @foreach($roundTypes as $t)<option value="{{ $t->id }}" @selected($old('round_type_id', $round->round_type_id) == $t->id)>{{ $t->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label fw-bold">Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ $old('sort_order', $round->exists ? $round->sort_order : '') }}" placeholder="auto"></div>

    <div class="col-md-6"><label class="form-label fw-bold">Quotas in this round</label>
        <select name="quotas[]" class="form-select" multiple>
            @foreach($quotas as $q)<option value="{{ $q->id }}" @selected(in_array($q->id, array_map('intval', $old('quotas', $round->quotas ?? []))))>{{ $q->name }}</option>@endforeach</select>
        <div class="form-text">Leave empty for all quotas of the cycle.</div></div>
    <div class="col-md-3"><label class="form-label fw-bold">Round Status</label>
        <select name="round_status" class="form-select">@foreach(\App\Models\Counselling\CounsellingCycle::STATUSES as $k => $v)
            <option value="{{ $k }}" @selected($old('round_status', $round->round_status ?? 'scheduled') === $k)>{{ $v }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label fw-bold">Status Note</label>
        <input type="text" name="status_note" class="form-control" value="{{ $old('status_note', $round->status_note) }}"></div>

    <div class="col-md-4"><label class="form-label fw-bold">Fresh registration</label>
        <select name="fresh_registration_allowed" class="form-select">
            @php $v = (string) $old('fresh_registration_allowed', $tri($round->fresh_registration_allowed)); @endphp
            <option value="" @selected($v === '')>Inherit from round type</option><option value="1" @selected($v === '1')>Allowed</option><option value="0" @selected($v === '0')>Not allowed</option></select></div>
    <div class="col-md-4"><label class="form-label fw-bold">Earlier choices carry forward</label>
        <select name="choices_carry_forward" class="form-select">
            @php $v = (string) $old('choices_carry_forward', $tri($round->choices_carry_forward)); @endphp
            <option value="" @selected($v === '')>Not specified</option><option value="1" @selected($v === '1')>Yes</option><option value="0" @selected($v === '0')>No</option></select></div>
    <div class="col-md-4"><label class="form-label fw-bold">Willingness options</label>
        <select name="willingness_options[]" class="form-select" multiple>@foreach(\App\Models\Counselling\CounsellingCycle::WILLINGNESS as $k => $l)
            <option value="{{ $k }}" @selected(in_array($k, $old('willingness_options', $round->willingness_options ?? [])))>{{ $l }}</option>@endforeach</select>
        <div class="form-text">Empty = inherit from the cycle.</div></div>

    <div class="col-md-6"><label class="form-label fw-bold">Notice URL</label>
        <input type="url" name="notice_url" class="form-control" value="{{ $old('notice_url', $round->notice_url) }}" placeholder="https://"></div>
    <div class="col-md-6"><label class="form-label fw-bold">Source URL</label>
        <input type="url" name="source_url" class="form-control" value="{{ $old('source_url', $round->source_url) }}" placeholder="https://"></div>
    <div class="col-12"><label class="form-label fw-bold">Instructions</label>
        <textarea name="instructions" rows="3" class="form-control">{{ $old('instructions', $round->instructions) }}</textarea></div>
</div>
