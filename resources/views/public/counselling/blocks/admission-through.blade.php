{{-- "Admission through": counsellings that fill seats at a college, with the latest closing value per category.
     Data: $college {id, name, slug}, $counsellings[]. Unstyled on purpose: style .cns-* on your site. --}}
<section class="cns-block cns-admission-through">
    <h2 class="cns-block__title">Admission to {{ $college['name'] }} through</h2>
    @forelse($counsellings as $block)
        <article class="cns-card">
            <h3 class="cns-card__title"><a href="{{ $block['counselling']['url'] }}">{{ $block['counselling']['name'] }}</a> <span class="cns-card__year">{{ $block['cycle']['year'] }}</span></h3>
            @foreach($block['courses'] as $course)
                <div class="cns-course">
                    <h4 class="cns-course__name">{{ $course['course']['name'] }}</h4>
                    <ul class="cns-course__quotas">
                        @foreach($course['quotas'] as $q)
                            <li>{{ $q['quota'] }}@if($q['institute_type']) · {{ $q['institute_type'] }}@endif
                                @if($q['total_seats']) · {{ $q['total_seats'] }} seats @endif
                                @if($q['annual_tuition_fee'] !== null) · Rs {{ number_format($q['annual_tuition_fee']) }} / year @endif
                                @if($q['bond_details']) · bond: {{ $q['bond_details'] }}@endif</li>
                        @endforeach
                    </ul>
                    @if(count($course['latest_cutoffs']))
                    <table class="cns-cutoffs">
                        <thead><tr><th>Round</th><th>Quota</th><th>Category</th><th>Closing</th></tr></thead>
                        <tbody>
                        @foreach($course['latest_cutoffs'] as $x)
                            <tr><td>{{ $x['round'] }}</td><td>{{ $x['quota'] }}</td>
                                <td>{{ $x['category'] }}@if($x['pwd']) (PwD)@endif @if($x['gender'] !== 'neutral') · {{ ucfirst($x['gender']) }} only @endif</td>
                                <td>{{ $x['closing_value'] !== null ? number_format($x['closing_value'], $x['rank_type'] === 'air' || str_contains($x['rank_type'], 'rank') ? 0 : 2) : '-' }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            @endforeach
        </article>
    @empty
        <p class="cns-block__empty">No counselling lists this college yet.</p>
    @endforelse
</section>
