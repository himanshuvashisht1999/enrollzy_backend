{{-- "Experts page" tab of the expert form (create and edit): what the website's experts list shows on the card and
     filters by. $expert is null on create. --}}
@php
    use App\Models\Expert;
    $v = fn ($k, $d = null) => old($k, $expert->{$k} ?? $d);
    $has = fn ($k, $opt) => in_array($opt, (array) old($k, $expert->{$k} ?? []), true);
@endphp
<div class="tab-pane fade" id="ex-listing">
    <h5 class="mb-1 text-primary">Experts page</h5>
    <p class="text-muted small">What the website's experts list shows on this expert's card and filters by. An empty field is simply not shown.</p>
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Helps with <small class="text-muted">(one line on the card)</small></label>
            <input type="text" class="form-control" name="helps_with" maxlength="200" value="{{ $v('helps_with') }}" placeholder="e.g. JEE strategy, college choice and JoSAA counselling">
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" maxlength="100" value="{{ $v('city') }}" placeholder="e.g. Chandigarh">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" class="form-control" name="state" maxlength="100" value="{{ $v('state') }}" placeholder="e.g. Punjab">
        </div>
        <div class="col-md-4">
            <label class="form-label">Students guided</label>
            <input type="number" min="0" class="form-control" name="no_of_students_counseled" value="{{ $v('no_of_students_counseled') }}" placeholder="e.g. 500">
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">Expertise</label>
            {{-- the managed list (Experts > Expertise areas), plus any hidden area this expert still has --}}
            @foreach(collect(\App\Models\ExpertiseArea::names())->merge($expert?->expertise_areas ?? [])->unique()->values() as $i => $o)
                <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="expertise_areas[]" id="ea{{ $i }}" value="{{ $o }}" @checked($has('expertise_areas', $o))><label class="form-check-label" for="ea{{ $i }}">{{ $o }}</label></div>
            @endforeach
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">Classes / levels they guide</label>
            @foreach(Expert::LEVELS as $i => $o)
                <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="levels[]" id="lv{{ $i }}" value="{{ $o }}" @checked($has('levels', $o))><label class="form-check-label" for="lv{{ $i }}">{{ $o }}</label></div>
            @endforeach
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">Consultation</label>
            @foreach(Expert::CONSULTATION as $i => $o)
                <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="session_modes[]" id="cm{{ $i }}" value="{{ $o }}" @checked($has('session_modes', $o))><label class="form-check-label" for="cm{{ $i }}">{{ $o }}</label></div>
            @endforeach
            <small class="text-muted d-block">Languages are set under "Language of teaching" (Teaching tab).</small>
        </div>
        <div class="col-md-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="offers_free_intro" id="offers_free_intro" value="1" @checked(old('offers_free_intro', $expert->offers_free_intro ?? false))>
                <label class="form-check-label" for="offers_free_intro">Offers a free intro call</label>
            </div>
            <div class="input-group mt-2" style="max-width: 220px">
                <input type="number" min="5" max="60" class="form-control" name="free_intro_minutes" value="{{ $v('free_intro_minutes', 15) }}">
                <span class="input-group-text">minutes</span>
            </div>
            <small class="text-muted d-block">The card then says "Book free intro". Open free (₹0) slots for it in Slots.</small>
        </div>
    </div>
</div>
