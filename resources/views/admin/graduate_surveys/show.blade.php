@extends('admin.layouts.master')

@section('title', 'Survey response: ' . $s->name)

{{-- One graduate survey response (GraduateSurveyController::show): every answer as asked, and the team's follow-up. --}}
@section('content')
<div class="container-fluid py-3">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.graduate-surveys.index') }}" class="small text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to responses</a>
    <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mb-3">
        <h4 class="mb-0 fw-bold">{{ $s->name }}</h4>
        <span class="badge {{ ['new' => 'bg-warning text-dark', 'contacted' => 'bg-primary', 'converted' => 'bg-success', 'not_interested' => 'bg-secondary'][$s->status] ?? 'bg-light' }}">{{ $statuses[$s->status] ?? $s->status }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Answers</div>
                <div class="card-body p-0"><table class="table mb-0">
                    @foreach($answers as $a)
                        <tr><td class="text-muted small" style="width:45%">{{ $a['q'] }}</td><td class="fw-semibold">{{ $a['a'] }}</td></tr>
                    @endforeach
                </table></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Contact</div>
                <div class="card-body small">
                    <div class="mb-1"><i class="fas fa-phone me-2 text-muted"></i><a href="tel:+91{{ $s->phone }}">+91 {{ $s->phone }}</a>
                        <a class="ms-2" href="https://wa.me/91{{ $s->phone }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a></div>
                    @if($s->email)<div class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $s->email }}</div>@endif
                    @if($s->current_state)<div class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $s->current_state }}</div>@endif
                    <div class="mb-1"><i class="fas fa-check-circle me-2 {{ $s->consent ? 'text-success' : 'text-muted' }}"></i>{{ $s->consent ? 'Agreed to be contacted' : 'No consent recorded' }}</div>
                    <hr>
                    @if($student)
                        <div><b>Already a student in the CRM:</b> #{{ $student->id }} {{ $student->name }}{{ $student->city ? ', ' . $student->city : '' }}</div>
                    @else
                        <div class="text-muted">Not a student in the CRM yet (no student with this mobile).</div>
                    @endif
                    <hr>
                    <div class="text-muted">First submitted {{ \Carbon\Carbon::parse($s->created_at)->format('d M Y, h:i A') }}
                        @if($s->submissions > 1)· submitted {{ $s->submissions }} times, latest {{ \Carbon\Carbon::parse($s->updated_at)->format('d M Y, h:i A') }}@endif</div>
                    @if($utm)<div class="text-muted mt-1">Came from: {{ collect($utm)->map(fn ($v, $k) => str_replace('utm_', '', $k) . ' ' . $v)->implode(' · ') }}</div>@endif
                </div>
            </div>
            <form method="POST" action="{{ route('admin.graduate-surveys.update', $s->id) }}" class="card border-0 shadow-sm">@csrf
                <div class="card-header bg-white fw-semibold">Follow-up</div>
                <div class="card-body">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm mb-2">@foreach($statuses as $k => $v)<option value="{{ $k }}" @selected($s->status === $k)>{{ $v }}</option>@endforeach</select>
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea name="notes" rows="4" class="form-control form-control-sm" placeholder="What was discussed, next step">{{ old('notes', $s->notes) }}</textarea>
                    <button class="btn btn-primary btn-sm mt-2 px-3">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
