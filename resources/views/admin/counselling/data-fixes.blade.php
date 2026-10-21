@extends('admin.layouts.master')

@section('title', 'Counselling data fixes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Counselling data fixes</h4>
        <p class="text-muted mb-0">One-time corrections to existing data. Check the list, then run them all with one click. Each fix runs once and is written to the audit log.</p>
    </div>
    <a href="{{ route('admin.counselling.counsellings.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Counselling</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body">
    @foreach($fixes as $key => $f)
        <div class="d-flex justify-content-between align-items-start border-top py-3 gap-3">
            <div>
                <div class="fw-semibold">{{ $f['title'] }}</div>
                <div class="small text-muted">{{ $f['detail'] }}</div>
            </div>
            @if($f['done'])
                <span class="badge bg-success-subtle text-success text-nowrap">Already run</span>
            @elseif($f['pending'])
                <span class="badge bg-warning text-dark text-nowrap">{{ $f['pending'] }} to fix</span>
            @else
                <span class="badge bg-light text-dark border text-nowrap">Nothing to fix</span>
            @endif
        </div>
    @endforeach
</div></div>

@php $pending = collect($fixes)->where('done', false)->count(); @endphp
@if($pending)
<form method="POST" action="{{ route('admin.counselling.data-fixes.run') }}" data-confirm="Run the data fixes now?">
    @csrf
    <button class="btn btn-primary"><i class="fas fa-play me-1"></i> Run data fixes</button>
</form>
@else
    <div class="alert alert-success">All data fixes have run.</div>
@endif
@endsection
