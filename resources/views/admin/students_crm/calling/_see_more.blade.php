{{-- Below a calling dashboard list: the page shows the first rows; "See more" adds the next batch (same filters). --}}
@if($total > $shown)
<div class="see-more-wrap text-center py-3 border-top">
    <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold see-more" data-part="{{ $part }}" data-shown="{{ $shown }}" data-total="{{ $total }}">
        <i class="fas fa-chevron-down me-1"></i> See more
    </button>
    <div class="small text-muted mt-1">Showing <span class="see-more-shown">{{ number_format($shown) }}</span> of {{ number_format($total) }} {{ $noun }}</div>
</div>
@endif
