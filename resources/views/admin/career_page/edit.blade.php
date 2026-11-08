@extends('admin.layouts.master')

@section('title', 'Career Roadmap Page')

@php
    $faqs = old('faqs', $content['faqs'] ?? []);
    $topics = old('topics', collect($content['topics'] ?? [])->map(fn ($t) => ['label' => $t['label'], 'keywords' => implode(', ', $t['keywords'] ?? [])])->all());
    $val = function ($key, $kind) use ($content) {
        $v = old("c.$key", $content[$key] ?? '');
        return is_array($v) ? implode("\n", $v) : $v;
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Career roadmap page</h4>
        <small class="text-muted">Texts of /career-roadmap/all and the roadmap pages. An empty field shows the website's default text.</small>
    </div>
    <a href="{{ route('admin.career-roadmap-sub-modules.index') }}" class="btn btn-outline-secondary"><i class="fas fa-map-signs me-1"></i> Roadmaps</a>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('admin.career-roadmap-page.update') }}">
    @csrf
    @method('PUT')
    @foreach($fields as $group => $list)
        <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
            <h5 class="mb-3">{{ $group }}</h5>
            <div class="row g-3">
                @foreach($list as $key => [$label, $kind, $help])
                    <div class="{{ $kind === 'text' ? 'col-md-6' : 'col-md-12' }}">
                        <label class="form-label fw-bold" for="c-{{ $key }}">{{ $label }}</label>
                        @if($kind === 'text')
                            <input class="form-control" id="c-{{ $key }}" name="c[{{ $key }}]" value="{{ $val($key, $kind) }}" maxlength="300">
                        @else
                            <textarea class="form-control" id="c-{{ $key }}" name="c[{{ $key }}]" rows="{{ $kind === 'lines' ? 3 : 2 }}">{{ $val($key, $kind) }}</textarea>
                        @endif
                        @if($help)<small class="text-muted">{{ $help }}</small>@endif
                    </div>
                @endforeach
            </div>

            @if($group === 'Read, ask and discuss')
                <h6 class="mt-4 mb-1">Topics</h6>
                <p class="small text-muted">The chips above the guides and questions. A guide or question belongs to a topic when its title has one of the keywords. The page opens on the topic of the stage, class and interest the student picked.</p>
                <div id="topics">
                    @foreach($topics as $i => $t)
                        <div class="row g-2 mb-2 rep-row"><div class="col-md-3"><input class="form-control" name="topics[{{ $i }}][label]" value="{{ $t['label'] ?? '' }}" placeholder="Topic"></div><div class="col-md-8"><input class="form-control" name="topics[{{ $i }}][keywords]" value="{{ $t['keywords'] ?? '' }}" placeholder="Keywords, separated by commas"></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger rep-del">×</button></div></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rep-add" data-target="topics" data-kind="topic"><i class="fas fa-plus"></i> Add a topic</button>
            @endif

            @if($group === 'FAQs')
                <div id="faqs" class="mt-3">
                    @foreach($faqs as $i => $f)
                        <div class="row g-2 mb-2 rep-row"><div class="col-md-4"><input class="form-control" name="faqs[{{ $i }}][q]" value="{{ $f['q'] ?? '' }}" placeholder="Question"></div><div class="col-md-7"><textarea class="form-control" name="faqs[{{ $i }}][a]" rows="2" placeholder="Answer">{{ $f['a'] ?? '' }}</textarea></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger rep-del">×</button></div></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rep-add" data-target="faqs" data-kind="faq"><i class="fas fa-plus"></i> Add a question</button>
            @endif
        </div></div>
    @endforeach
    <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save</button>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var add = e.target.closest('.rep-add');
        if (add) {
            var box = document.getElementById(add.dataset.target), i = Date.now(), d = document.createElement('div');
            d.className = 'row g-2 mb-2 rep-row';
            d.innerHTML = add.dataset.kind === 'faq'
                ? '<div class="col-md-4"><input class="form-control" name="faqs[' + i + '][q]" placeholder="Question"></div><div class="col-md-7"><textarea class="form-control" name="faqs[' + i + '][a]" rows="2" placeholder="Answer"></textarea></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger rep-del">×</button></div>'
                : '<div class="col-md-3"><input class="form-control" name="topics[' + i + '][label]" placeholder="Topic"></div><div class="col-md-8"><input class="form-control" name="topics[' + i + '][keywords]" placeholder="Keywords, separated by commas"></div><div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger rep-del">×</button></div>';
            box.appendChild(d);
        }
        var del = e.target.closest('.rep-del'); if (del) del.closest('.rep-row').remove();
    });
</script>
@endpush
