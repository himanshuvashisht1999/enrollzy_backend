{{-- "Counselling after this exam". Data: $exam {name, slug}, $counsellings[]. Unstyled on purpose: style .cns-* on your site. --}}
<section class="cns-block cns-exam-counselling">
    <h2 class="cns-block__title">Counselling after {{ $exam['name'] }}</h2>
    @forelse($counsellings as $c)
        <article class="cns-card cns-card--{{ $c['status']['state'] }}">
            <h3 class="cns-card__title"><a href="{{ $c['url'] }}">{{ $c['name'] }}</a></h3>
            <p class="cns-card__meta">
                {{ ucfirst($c['level']) }}@if($c['conducting_body']) · {{ $c['conducting_body'] }}@endif
                @if($c['role'] === 'additional') · additional exam @endif
                @if($c['current_cycle']) · {{ $c['current_cycle']['year'] }}@endif
            </p>
            <p class="cns-card__status">{{ $c['status']['text'] }}</p>
            @if($c['seat_scope_note'])<p class="cns-card__note">{{ $c['seat_scope_note'] }}</p>@endif
        </article>
    @empty
        <p class="cns-block__empty">No counselling has been listed for this exam yet.</p>
    @endforelse
</section>
