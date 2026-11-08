@extends('admin.layouts.master')

@section('title', 'Notice Board')

{{-- Notice Board (NoticeController): post (admins, team leads), read, acknowledge, read receipts. --}}
@section('content')
@include('admin.calling._styles')
@php $me = auth()->user(); @endphp
<div class="cc">
    <div class="top">
        <div><h1>Notice Board</h1><div class="sub">Announcements for the calling team, with acknowledgement</div></div>
        <div class="grow"></div>
        <a class="btn sm" href="{{ route('admin.calling.notices.index', $showExpired ? [] : ['expired' => 1]) }}">{{ $showExpired ? 'Hide expired' : 'Show expired' }}</a>
    </div>

    <div class="grid {{ $canPost ? 'g2' : '' }}" style="{{ $canPost ? 'grid-template-columns:minmax(0,1.35fr) minmax(0,1fr)' : '' }}">
        <div class="card">
            <div class="hd"><h3>📌 Notices</h3><span class="muted sm">{{ $list->count() }}</span></div>
            <div class="bd">
                @forelse($list as $notice)
                    @include('admin.calling._notice', ['n' => $notice, 'nb' => $n, 'withDelete' => true])
                @empty
                    <div class="empty">No notices yet.</div>
                @endforelse
            </div>
        </div>

        @if($canPost)
        <div class="card" style="align-self:start">
            <div class="hd"><h3>✍️ Post a notice</h3></div>
            <div class="bd">
                @if($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('admin.calling.notices.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label small fw-semibold">To</label>
                        <select name="audience" class="form-select custom-select2" required>@foreach($audiences as $k => $l)<option value="{{ $k }}" @selected(old('audience') === $k)>{{ $l }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Title</label><input type="text" name="title" class="form-control" maxlength="200" value="{{ old('title') }}" required placeholder="e.g. Clear overdue follow-ups before lunch"></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Message</label><textarea name="body" class="form-control" rows="5" maxlength="5000" required>{{ old('body') }}</textarea></div>
                    <div class="row g-2 mb-3" style="display:flex">
                        <div class="col-6"><label class="form-label small fw-semibold">Priority</label>
                            <select name="priority" class="form-select custom-select2">@foreach(\App\Models\Notice::PRIORITIES as $k => $l)<option value="{{ $k }}" @selected(old('priority', 'normal') === $k)>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-6"><label class="form-label small fw-semibold">Show until <span class="text-muted fw-normal">(optional)</span></label><input type="date" name="expires_on" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('expires_on') }}"></div>
                    </div>
                    <div class="form-check form-switch mb-1"><input class="form-check-input" type="checkbox" name="require_ack" value="1" id="nAck" @checked(old('require_ack'))><label class="form-check-label" for="nAck">Ask everyone to acknowledge</label></div>
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="pinned" value="1" id="nPin" @checked(old('pinned'))><label class="form-check-label" for="nPin">Pin to the top</label></div>
                    <p class="small text-muted">Important and urgent notices that need acknowledging pop up when people open their dashboard.</p>
                    <button class="btn pri" style="width:100%;justify-content:center">Post notice</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@include('admin.calling._scripts', ['noPrompt' => true, 'unacked' => $n->unacked($me), 'notices' => $n])
@endsection
