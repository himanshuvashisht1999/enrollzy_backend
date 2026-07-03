@extends('admin.layouts.master')

@section('title', 'Hero Sliders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold">Hero Images Slider</h3>
        <p class="text-muted mb-2">Upload and manage rotating images for the homepage hero section.</p>
        <div class="alert alert-info py-2 mb-0 d-inline-block shadow-sm">
            <i class="fas fa-info-circle me-1"></i> <strong>Recommended Banner Size:</strong> 1920 x 800 pixels for best fit on the homepage. Max 5MB.
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="form-check form-switch bg-white rounded-pill px-4 py-2 shadow-sm border">
            <input class="form-check-input global-banner-toggle" type="checkbox" role="switch" id="globalBannerToggle"
                {{ isset($setting) && $setting->is_show_full_banner ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="globalBannerToggle">Full Banner Mode</label>
        </div>
        <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addImageModal">
            <i class="fas fa-plus me-2"></i>Add New Image
        </button>
    </div>
</div>

<div class="row g-4">
    @forelse($sliders as $slider)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm overflow-hidden">
                <div class="position-relative">
                    <img src="{{ asset($slider->image_path) }}" class="card-img-top" alt="Hero Image" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 p-2">
                        <span class="badge bg-dark shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas {{ $slider->image_type == 'Full Banner' ? 'fa-image' : 'fa-font' }} me-1"></i>
                            {{ $slider->image_type ?? 'Text' }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if($slider->image_type == 'Text')
                        <h6 class="fw-bold text-truncate mb-1">{{ $slider->heading }}</h6>
                        <p class="small text-muted text-truncate mb-2">{{ $slider->subheading }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted fw-bold">Order: {{ $slider->sort_order }}</small>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 edit-slider-btn" 
                                data-slider="{{ json_encode($slider) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.hero-sliders.destroy', $slider) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-images fa-3x text-light mb-3"></i>
                    <p class="text-muted">No images added to the slider yet.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.hero-sliders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold">Add Hero Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Image Content Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check custom-option w-100">
                                <input class="form-check-input type-selector" type="radio" name="image_type" id="typeTextAdd" value="Text" checked>
                                <label class="form-check-label w-100" for="typeTextAdd">
                                    <span class="d-block fw-bold text-primary"><i class="fas fa-font me-2"></i>Text Overlay</span>
                                    <small class="text-muted">Shows title, description & buttons over image</small>
                                </label>
                            </div>
                            <div class="form-check custom-option w-100">
                                <input class="form-check-input type-selector" type="radio" name="image_type" id="typeBannerAdd" value="Full Banner">
                                <label class="form-check-label w-100" for="typeBannerAdd">
                                    <span class="d-block fw-bold text-success"><i class="fas fa-image me-2"></i>Full Banner</span>
                                    <small class="text-muted">Just the image, no text content</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Background Image</label>
                                <input type="file" name="image_path" class="form-control" required>
                                <div class="form-text">Recommended size: 1920x800px. Max 5MB.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="text-overlay-fields mt-3 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-pen-nib me-2"></i>Content Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Main Heading</label>
                                <input type="text" name="heading" class="form-control" placeholder="e.g. Dream Big, Learn More">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Sub-Heading / Description</label>
                                <textarea name="subheading" class="form-control editor" rows="3" placeholder="Brief description goes here..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Button Text</label>
                                <input type="text" name="button_text" class="form-control" placeholder="e.g. Enroll Now">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Button Link (URL)</label>
                                <input type="text" name="button_url" class="form-control" placeholder="e.g. /apply-now">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase">Badge Text</label>
                                <input type="text" name="badge_text" class="form-control" placeholder="e.g. India's no.1 Education Market place">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-chart-bar me-2"></i>Statistics (Optional)</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 1 Count</label>
                                <input type="text" name="stat_1_count" class="form-control" placeholder="e.g. 2800+">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 1 Label</label>
                                <input type="text" name="stat_1_label" class="form-control" placeholder="e.g. Institution">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 2 Count</label>
                                <input type="text" name="stat_2_count" class="form-control" placeholder="e.g. 1.2L+">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 2 Label</label>
                                <input type="text" name="stat_2_label" class="form-control" placeholder="e.g. Student Enrolled">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 3 Count</label>
                                <input type="text" name="stat_3_count" class="form-control" placeholder="e.g. 4500+">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 3 Label</label>
                                <input type="text" name="stat_3_label" class="form-control" placeholder="e.g. Scholarship's">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Slider</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Image Modal -->
<div class="modal fade" id="editSliderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" enctype="multipart/form-data" id="editSliderForm">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold">Edit Hero Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Image Content Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check custom-option w-100">
                                <input class="form-check-input type-selector" type="radio" name="image_type" id="typeTextEdit" value="Text">
                                <label class="form-check-label w-100" for="typeTextEdit">
                                    <span class="d-block fw-bold text-primary"><i class="fas fa-font me-2"></i>Text Overlay</span>
                                </label>
                            </div>
                            <div class="form-check custom-option w-100">
                                <input class="form-check-input type-selector" type="radio" name="image_type" id="typeBannerEdit" value="Full Banner">
                                <label class="form-check-label w-100" for="typeBannerEdit">
                                    <span class="d-block fw-bold text-success"><i class="fas fa-image me-2"></i>Full Banner</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Change Image (Optional)</label>
                                <input type="file" name="image_path" class="form-control">
                                <div class="form-text">Recommended size: 1920x800px. Max 5MB.</div>
                                <div id="currentImageThumb" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sort Order</label>
                                <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="text-overlay-fields mt-3 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-pen-nib me-2"></i>Content Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Main Heading</label>
                                <input type="text" name="heading" id="edit_heading" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Sub-Heading / Description</label>
                                <textarea name="subheading" id="edit_subheading" class="form-control editor" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Button Text</label>
                                <input type="text" name="button_text" id="edit_button_text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Button Link (URL)</label>
                                <input type="text" name="button_url" id="edit_button_url" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase">Badge Text</label>
                                <input type="text" name="badge_text" id="edit_badge_text" class="form-control">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-chart-bar me-2"></i>Statistics (Optional)</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 1 Count</label>
                                <input type="text" name="stat_1_count" id="edit_stat_1_count" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 1 Label</label>
                                <input type="text" name="stat_1_label" id="edit_stat_1_label" class="form-control">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 2 Count</label>
                                <input type="text" name="stat_2_count" id="edit_stat_2_count" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 2 Label</label>
                                <input type="text" name="stat_2_label" id="edit_stat_2_label" class="form-control">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 3 Count</label>
                                <input type="text" name="stat_3_count" id="edit_stat_3_count" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Stat 3 Label</label>
                                <input type="text" name="stat_3_label" id="edit_stat_3_label" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Slider</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<style>
    .custom-option {
        padding: 15px;
        border: 2px solid #edf2f7;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
    }
    .custom-option:hover {
        background-color: #f7fafc;
        border-color: #cbd5e0;
    }
    .custom-option input:checked + label {
        color: inherit;
    }
    .custom-option:has(input:checked) {
        border-color: #3182ce;
        background-color: #ebf8ff;
        box-shadow: 0 0 0 1px #3182ce;
    }
</style>
<script>
$(document).ready(function() {
    // Initialize Editor
    if (typeof initializeTinyMCE === 'function') {
        initializeTinyMCE('.editor', 200);
    }

    // Handle conditional fields for Add/Edit modals
    function toggleFields(modal) {
        const type = modal.find('.type-selector:checked').val();
        if (type === 'Text') {
            modal.find('.text-overlay-fields').slideDown();
        } else {
            modal.find('.text-overlay-fields').slideUp();
        }
    }

    $('.type-selector').on('change', function() {
        toggleFields($(this).closest('.modal'));
    });

    // Initial check on modal show
    $('#addImageModal').on('shown.bs.modal', function() {
        toggleFields($(this));
    });

    // Edit Slider Logic
    $('.edit-slider-btn').on('click', function() {
        const slider = $(this).data('slider');
        const modal = $('#editSliderModal');
        const form = $('#editSliderForm');

        // Set action URL
        form.attr('action', `{{ url('admin/hero-sliders') }}/${slider.id}`);

        // Set Image Type
        modal.find(`.type-selector[value="${slider.image_type}"]`).prop('checked', true);
        
        // Fill fields
        modal.find('#edit_sort_order').val(slider.sort_order);
        modal.find('#edit_heading').val(slider.heading);
        modal.find('#edit_subheading').val(slider.subheading);
        modal.find('#edit_button_text').val(slider.button_text);
        modal.find('#edit_button_url').val(slider.button_url);
        modal.find('#edit_badge_text').val(slider.badge_text);
        modal.find('#edit_stat_1_count').val(slider.stat_1_count);
        modal.find('#edit_stat_1_label').val(slider.stat_1_label);
        modal.find('#edit_stat_2_count').val(slider.stat_2_count);
        modal.find('#edit_stat_2_label').val(slider.stat_2_label);
        modal.find('#edit_stat_3_count').val(slider.stat_3_count);
        modal.find('#edit_stat_3_label').val(slider.stat_3_label);

        // Set TinyMCE content for Edit Modal
        if (tinymce.get('edit_subheading')) {
            tinymce.get('edit_subheading').setContent(slider.subheading || '');
        }

        // Show image thumb
        modal.find('#currentImageThumb').html(`<img src="{{ asset('') }}${slider.image_path}" class="img-thumbnail" style="height: 60px;">`);

        toggleFields(modal);
        modal.modal('show');
    });

    // Global Banner Mode Toggle
    $('.global-banner-toggle').on('change', function() {
        const isChecked = $(this).is(':checked');
        $.ajax({
            url: `{{ route('admin.hero-sliders.global-banner-toggle') }}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: isChecked
            },
            success: function(response) {
                if(response.success) {
                    toastr.success('Global banner mode updated');
                }
            }
        });
    });
});
</script>
@endpush
