{{-- Page header: title, subtitle, the range switch (when $rangeRoute is given), a live clock and the notice bell. --}}
<div class="top">
    <div><h1>{{ $title }}</h1><div class="sub">{{ $sub }}</div></div>
    <div class="grow"></div>
    @isset($rangeRoute)
        <div class="seg" role="group" aria-label="Range">
            @foreach(\App\Support\Calling\CommandCenter::RANGES as $k => $l)<a href="{{ route($rangeRoute, ['range' => $k]) }}" class="{{ $range === $k ? 'on' : '' }}">{{ $l }}</a>@endforeach
        </div>
    @endisset
    {{ $slot ?? '' }}
    <a href="{{ route('admin.calling.notices.index') }}" class="bell" title="Notice Board">📌 @if($unacked->count())<b>{{ $unacked->count() }}</b>@endif</a>
    <span class="clock" data-cc-clock>{{ now()->format('H:i:s') }}</span>
</div>
