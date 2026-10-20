@extends('admin.layouts.master')

@section('title', 'Rankings & Accreditation: data update')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Rankings &amp; Accreditation: data update</h4>
        <p class="text-muted mb-0">Run this after the SQL file has created the tables. It reads the data that is in this database <em>right now</em>, adds what is missing, and never overwrites anything. You can run it again any time to pick up newer data.</p>
    </div>
    <a href="{{ route('admin.quality.dashboard') }}" class="btn btn-light border">Dashboard</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

@if($results)
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success"><div class="card-body">
        <div class="fw-bold mb-2 text-success"><i class="fas fa-check-circle me-1"></i> Update finished</div>
        @foreach($results as $r)
            <div class="mb-2"><strong>{{ $r['title'] }}</strong><div class="text-muted small">{{ $r['result'] }}</div>
                @if(!empty($r['warnings']))<div class="small text-warning">@foreach(array_slice($r['warnings'], 0, 10) as $w)<div>{{ $w }}</div>@endforeach @if(count($r['warnings']) > 10)<div>…and {{ count($r['warnings']) - 10 }} more</div>@endif</div>@endif
            </div>
        @endforeach
        <a href="{{ route('admin.quality.review') }}" class="btn btn-sm btn-primary mt-2">Go to the review queue</a>
    </div></div>
@endif

@if($missing)
    <div class="alert alert-danger">
        <strong>The database structure is not ready.</strong> Run <code>database/sql/2026_10_21_rankings_accreditation_update.sql</code> first.
        <div class="small mt-1">Missing: {{ implode(', ', $missing) }}</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
        <div class="fw-bold mb-3">What will happen now</div>
        @foreach($steps as $i => $s)
            <div class="d-flex gap-3 mb-3">
                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:32px;height:32px;flex:none">{{ $i + 1 }}</div>
                <div><div class="fw-bold">{{ $s['title'] }}</div><div class="text-muted small">{{ $s['detail'] }}</div>
                    @if(!empty($s['warnings']))<div class="small text-warning mt-1">@foreach(array_slice($s['warnings'], 0, 5) as $w)<div>{{ $w }}</div>@endforeach</div>@endif</div>
            </div>
        @endforeach
        <form method="POST" action="{{ route('admin.quality.setup.run') }}" onsubmit="return confirm('Run the data update now? It only adds missing data and can be run again later.')">
            @csrf
            <button class="btn btn-success"><i class="fas fa-play me-1"></i> Run update</button>
            <span class="text-muted small ms-2">Take a database backup first if this is the live server.</span>
        </form>
    </div></div>
@endif
@endsection
