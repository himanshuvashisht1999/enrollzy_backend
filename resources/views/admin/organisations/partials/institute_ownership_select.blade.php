{{-- Institute section: Ownership Type from the master list (Masters → Ownership Types); an older value not in the list still shows --}}
@php
    $o = $organisation ?? null;
    $current = old('ownership_type', $o?->ownership_type);
    $choices = \App\Support\Institutes\InstituteFields::options($o)['ownershipTypes'];
@endphp
<select name="ownership_type" class="form-select" id="inst_ownership_type">
    <option value="">Select Ownership</option>
    @foreach($choices as $title)
        <option value="{{ $title }}" {{ $current === $title ? 'selected' : '' }}>{{ $title }}</option>
    @endforeach
</select>
@if(auth()->user()?->can('organisations-edit'))
    <div class="form-text"><a href="{{ route('admin.ownership-types.index') }}" target="_blank">Manage ownership types</a></div>
@endif
