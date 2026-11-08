{{-- Photos (upload) and videos (YouTube / Vimeo links) of an institute or branch: $media = its OrganisationMedia rows,
     $prefix = a unique id prefix. Saved with the form by InstituteFields::syncMedia (all inputs skip field autosave). --}}
@php use App\Support\Institutes\InstituteFields as IF_; @endphp
<input type="hidden" name="has_gallery_form" value="1">
<div class="col-12">
    @if($media->count())
        <div class="row g-2 mb-3">
            @foreach($media as $m)
                <div class="col-6 col-md-3">
                    <div class="border rounded p-2 h-100 bg-light">
                        @if($m->type === 'photo')
                            <a href="{{ asset($m->file_path) }}" target="_blank"><img src="{{ asset($m->file_path) }}" class="img-fluid rounded mb-2" style="height:110px;width:100%;object-fit:cover" alt=""></a>
                        @else
                            @php $thumb = IF_::videoThumb($m->video_url); @endphp
                            <a href="{{ $m->video_url }}" target="_blank" rel="noopener" class="d-block mb-2 position-relative">
                                @if($thumb)<img src="{{ $thumb }}" class="img-fluid rounded" style="height:110px;width:100%;object-fit:cover" alt="">
                                @else<div class="d-flex align-items-center justify-content-center rounded bg-dark text-white" style="height:110px">Video</div>@endif
                                <span class="badge bg-danger position-absolute top-0 start-0 m-1"><i class="bi bi-play-fill"></i> Video</span>
                            </a>
                            <input type="url" name="gallery[{{ $m->id }}][url]" class="form-control form-control-sm mb-1 no-autosave" value="{{ $m->video_url }}" maxlength="255">
                        @endif
                        <input type="text" name="gallery[{{ $m->id }}][caption]" class="form-control form-control-sm mb-1 no-autosave" value="{{ $m->caption }}" placeholder="Caption" maxlength="255">
                        <div class="form-check small">
                            <input class="form-check-input no-autosave" type="checkbox" name="gallery[{{ $m->id }}][remove]" value="1" id="{{ $prefix }}-rm-{{ $m->id }}">
                            <label class="form-check-label text-danger" for="{{ $prefix }}-rm-{{ $m->id }}">Remove on save</label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Add Photos</label>
            <input type="file" name="gallery_photos[]" class="form-control no-autosave" accept="image/jpeg,image/png,image/webp" multiple>
            <div class="form-text">JPG, PNG or WebP, up to 4 MB each. You can pick several at once.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Add Videos (YouTube / Vimeo links)</label>
            <div id="{{ $prefix }}-videos">
                <div class="input-group mb-2">
                    <input type="url" name="gallery_videos[0][url]" class="form-control no-autosave" placeholder="https://www.youtube.com/watch?v=…" maxlength="255">
                    <input type="text" name="gallery_videos[0][caption]" class="form-control no-autosave" placeholder="Caption" maxlength="255">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-video="{{ $prefix }}-videos">+ Add another video</button>
        </div>
    </div>
    <div class="form-text">Photos and videos are saved when you click the main Save / Update button.</div>
</div>

@once
@push('scripts')
<script>
    // "+ Add another video": one more link + caption row
    document.addEventListener('click', function (e) {
        const b = e.target.closest('[data-add-video]');
        if (!b) return;
        const box = document.getElementById(b.dataset.addVideo), i = box.querySelectorAll('.input-group').length;
        const row = document.createElement('div');
        row.className = 'input-group mb-2';
        row.innerHTML = '<input type="url" name="gallery_videos[' + i + '][url]" class="form-control no-autosave" placeholder="https://www.youtube.com/watch?v=…" maxlength="255">'
            + '<input type="text" name="gallery_videos[' + i + '][caption]" class="form-control no-autosave" placeholder="Caption" maxlength="255">';
        box.appendChild(row);
    });
</script>
@endpush
@endonce
