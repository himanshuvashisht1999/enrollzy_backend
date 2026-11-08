{{-- "Content" tab of the expert form (create and edit), in titled groups: videos, articles, social media profiles and the
     expert's own website. The website's "Become an expert" form asks for the same, so an approved application fills them.
     The add / remove script for article rows is at the end of each form (#add-article-btn, #article-row-template). --}}
@php
    $v = fn ($k) => old($k, $expert->{$k} ?? '');
    $vids = array_values(array_filter((array) old('demo_lecture_videos', $expert->demo_lecture_videos ?? []), fn ($u) => is_scalar($u) && trim((string) $u) !== ''));
    $articles = array_values((array) old('articles_written', $expert->articles_written ?? []));
@endphp
<div class="tab-pane fade" id="ex-content">
    <h5 class="mb-1 text-primary">Content &amp; links</h5>
    <p class="text-muted small">Shown on the expert's profile: videos and articles under "Videos &amp; articles", the profiles and website as links.</p>

    <h6 class="mt-3 mb-2 fw-bold"><i class="fas fa-video me-1 text-muted"></i> Videos</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Intro video</label>
            <input type="url" class="form-control" name="intro_video_url" maxlength="300" value="{{ $v('intro_video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
            <div class="form-text">A short video where the expert introduces themselves.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Sample session / lecture videos <small class="text-muted">(paste a link, press Enter)</small></label>
            <select class="form-control select2-tags" name="demo_lecture_videos[]" multiple>@foreach($vids as $u)<option value="{{ $u }}" selected>{{ $u }}</option>@endforeach</select>
        </div>
    </div>

    <h6 class="mt-4 mb-2 fw-bold"><i class="fas fa-newspaper me-1 text-muted"></i> Articles</h6>
    <div id="articles-container">
        @foreach($articles as $ai => $art)
            @php $art = is_array($art) ? $art : ['title' => '', 'url' => $art]; @endphp
            <div class="row g-2 mb-2 article-row align-items-end">
                <div class="col-md-5"><input type="text" name="articles_written[{{ $ai }}][title]" class="form-control" placeholder="Title, e.g. NIT vs IIIT: how to choose" value="{{ $art['title'] ?? '' }}"></div>
                <div class="col-md-6"><input type="url" name="articles_written[{{ $ai }}][url]" class="form-control" placeholder="https://..." value="{{ $art['url'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-article-btn" title="Remove"><i class="fas fa-trash"></i></button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-article-btn"><i class="fas fa-plus me-1"></i> Add article</button>
    <template id="article-row-template">
        <div class="row g-2 mb-2 article-row align-items-end">
            <div class="col-md-5"><input type="text" name="articles_written[INDEX][title]" class="form-control" placeholder="Title, e.g. NIT vs IIIT: how to choose" value=""></div>
            <div class="col-md-6"><input type="url" name="articles_written[INDEX][url]" class="form-control" placeholder="https://..." value=""></div>
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-article-btn" title="Remove"><i class="fas fa-trash"></i></button></div>
        </div>
    </template>

    <h6 class="mt-4 mb-2 fw-bold"><i class="fas fa-share-alt me-1 text-muted"></i> Social media profiles</h6>
    <div class="row g-3">
        @foreach(\App\Models\Expert::SOCIAL as $k => $label)
            <div class="col-md-6">
                <label class="form-label">{{ $label }}</label>
                <input type="url" class="form-control" name="{{ $k }}" maxlength="300" value="{{ $v($k) }}" placeholder="https://">
            </div>
        @endforeach
    </div>

    <h6 class="mt-4 mb-2 fw-bold"><i class="fas fa-globe me-1 text-muted"></i> Website</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Personal or company website</label>
            <input type="url" class="form-control" name="website_url" maxlength="300" value="{{ $v('website_url') }}" placeholder="https://">
        </div>
    </div>
</div>
