@extends('admin.layouts.master')

@section('title', 'Broadcast WhatsApp Message')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<style>
    .bootstrap-tagsinput { width: 100%; min-height: 45px; border-radius: 0.5rem; display: flex; align-items: center; flex-wrap: wrap; border-color: #dee2e6; }
    .bootstrap-tagsinput .tag { margin: 2px; background: #0d6efd; color: #fff; border-radius: 4px; padding: 4px 10px; font-weight: 500; }
    .whatsapp-preview { border: 1px solid #e0e0e0; border-radius: 1rem; padding: 1rem; background: #f0f2f5; position: sticky; top: 1rem; }
    .wa-bubble { background: #fff; padding: 0.75rem; border-radius: 0 0.5rem 0.5rem 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.1); position: relative; max-width: 90%; font-size: 0.9rem; line-height: 1.4; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">Broadcast Configuration</h6>
                </div>
                <div class="card-body">
                    <form id="broadcastForm" action="{{ route('admin.hr.whatsapp_template.postSendMessage') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Min Time Gap (sec)</label>
                                <input type="number" class="form-control rounded-3" name="min_time_gap" value="10" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Max Time Gap (sec)</label>
                                <input type="number" class="form-control rounded-3" name="max_time_gap" value="50" min="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Batch Size</label>
                                <input type="number" class="form-control rounded-3" name="batch_size" value="10" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Batch Gap (sec)</label>
                                <input type="number" class="form-control rounded-3" name="batch_gap" value="60" min="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Media Upload (Optional)</label>
                                <input type="file" class="form-control rounded-3" name="image" id="imgUpload" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Start Time</label>
                                <input type="datetime-local" class="form-control rounded-3" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Target User Categories</label>
                                <select name="user_categories[]" id="catSelect" class="form-select rounded-3" multiple>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->users_count }} users)</option>
                                        @foreach($cat->childrenRecursive as $child)
                                            <option value="{{ $child->id }}">-- {{ $child->name }} ({{ $child->users_count }} users)</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Manual Number Input</label>
                                <input type="text" id="numbers" name="numbers" data-role="tagsinput" class="form-control rounded-3" placeholder="Enter number & press Enter">
                                <div id="numberCount" class="small mt-2 text-primary fw-bold">Total Recipients: 0</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Message Text</label>
                                <textarea name="message" class="form-control rounded-3" rows="6" id="waMessage">{{ $whatsapp_template->message }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Media Caption</label>
                                <input type="text" name="caption" class="form-control rounded-3" id="waCaption" value="{{ $whatsapp_template->caption }}">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <a href="{{ route('admin.hr.whatsapp_template.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" form="broadcastForm" class="btn btn-primary rounded-pill px-5 fw-bold">Launch Broadcast</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="whatsapp-preview shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" height="20" class="me-2">
                    <span class="fw-bold small">Preview</span>
                </div>
                <div id="mediaPreviewCont" class="mb-2 d-none">
                    <img id="mediaPreview" class="img-fluid rounded-3 mb-2 shadow-sm" src="">
                </div>
                <div class="wa-bubble">
                    <div id="previewMessage" style="white-space: pre-wrap;">{{ $whatsapp_template->message }}</div>
                    <div id="previewCaption" class="mt-2 fw-bold small text-muted d-none"></div>
                    <div class="text-end" style="font-size: 0.65rem; color: #888;">{{ date('H:i') }} <i class="fas fa-check-double ms-1"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<script>
    $(document).ready(function() {
        // Tag Logic
        function updateCount() {
            let count = $('#numbers').tagsinput('items').length;
            $('#numberCount').text('Total Recipients: ' + count);
        }

        $('#numbers').on('itemAdded', updateCount);
        $('#numbers').on('itemRemoved', updateCount);

        // Preview Logic
        $('#waMessage').on('input', function() {
            $('#previewMessage').text($(this).val() || 'Message comes here...');
        });

        $('#waCaption').on('input', function() {
            let val = $(this).val();
            if(val) {
                $('#previewCaption').text(val).removeClass('d-none');
            } else {
                $('#previewCaption').addClass('d-none');
            }
        });

        $('#imgUpload').on('change', function() {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#mediaPreview').attr('src', e.target.result);
                    $('#mediaPreviewCont').removeClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Category Selection AJAX
        $('#catSelect').on('change', function() {
            let ids = $(this).val();
            if (!ids || ids.length === 0) return;

            $.ajax({
                url: "{{ route('admin.hr.whatsapp_template.getCategoryNumbers') }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", category_ids: ids },
                success: function(resp) {
                    $('#numbers').tagsinput('removeAll');
                    resp.numbers.forEach(num => $('#numbers').tagsinput('add', num));
                    updateCount();
                }
            });
        });
    });
</script>
@endpush
