@extends('admin.layouts.master')

@section('title', 'Add New Dynamic Exam')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Add Dynamic Exam</h4>
            <p class="text-muted mb-0">First, enter basic identity details. You will build its dynamic schema on the next
                page.</p>
        </div>
        <a href="{{ route('admin.dynamic-exams.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @push('css')
        <style>
        </style>
    @endpush

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.dynamic-exams.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. UPSC Civil Services"
                                    value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Short Name</label>
                                <input type="text" name="short_name" class="form-control" placeholder="e.g. UPSC"
                                    value="{{ old('short_name') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Type</label>
                                <select name="exam_type" class="form-select select2">
                                    <option value="">Select Type</option>
                                    @foreach(['National', 'State', 'University-Level', 'International', 'School-Level'] as $opt)
                                        <option value="{{ $opt }}" {{ old('exam_type') == $opt ? 'selected' : '' }}>{{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Category</label>
                                <select name="exam_category[]" class="form-select select2" multiple
                                    data-placeholder="Select Category">
                                    @foreach(['Engineering', 'Medical', 'Management', 'Law', 'School Admission', 'Arts', 'Commerce', 'Civil Services', 'Design', 'Media & Journalism'] as $opt)
                                        <option value="{{ $opt }}" {{ in_array($opt, old('exam_category', [])) ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Conducting Body Type</label>
                                <select name="conducting_body_type" class="form-select">
                                    <option value="">Select Body</option>
                                    @foreach(['Government', 'Private Body', 'University'] as $opt)
                                        <option value="{{ $opt }}" {{ old('conducting_body_type') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam Frequency</label>
                                <select name="exam_frequency" class="form-select">
                                    <option value="">Select Frequency</option>
                                    @foreach(['Once a Year', 'Twice a Year', 'Multiple Times', 'Other'] as $opt)
                                        <option value="{{ $opt }}" {{ old('exam_frequency') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Conducting Authority Name</label>
                                <input type="text" name="conducting_authority_name" class="form-control"
                                    placeholder="e.g. UPSC" value="{{ old('conducting_authority_name') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Exam Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cover Image</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                            </div>

                            <div class="col-md-12 mt-4">
                                <hr>
                                <h5 class="fw-bold">Ownership (Internal vs External)</h5>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Source Type</label>
                                <select name="exam_source_type" class="form-select">
                                    <option value="External">External (General)</option>
                                    <option value="Internal">Internal (Owned by Org)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Owning Organisation (If Internal)</label>
                                @php $orgs = \App\Models\Organisation::all(); @endphp
                                <select name="owning_organisation_id" class="form-select select2">
                                    <option value="">Select Organisation</option>
                                    @foreach($orgs as $org)
                                        <option value="{{ $org->id }}" data-type="{{ $org->organisation_type_id }}">
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label">About Exam</label>
                                <textarea name="about_exam" class="form-control editor">{{ old('about_exam') }}</textarea>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">Official Website</label>
                                <input type="url" name="official_website" class="form-control" placeholder="https://..."
                                    value="{{ old('official_website') }}">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="Public">Public</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Private">Private</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Upcoming">Upcoming</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>

                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured_exam" value="1">
                                    <label class="form-check-label">Featured Exam</label>
                                </div>
                            </div>

                            <div class="col-md-3 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_stages" value="1">
                                    <label class="form-check-label">Does this exam has stages?</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Continue to Schema Builder <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // $(document).ready(function () {
        //     if (typeof $.fn.select2 !== 'undefined') {
        //         $('.select2').select2({
        //             width: '100%'
        //         });
        //     }

        //     if (typeof initializeTinyMCE === 'function') {
        //         initializeTinyMCE('.editor');
        //     }

        // });

        let allOptions = [];

        $(document).ready(function () {

            // store all original options
            $('select[name="owning_organisation_id"] option').each(function () {
                allOptions.push({
                    value: $(this).val(),
                    text: $(this).text(),
                    type: $(this).data('type')
                });
            });

            function filterOrganisations() {
                let sourceType = $('select[name="exam_source_type"]').val();
                let $select = $('select[name="owning_organisation_id"]');

                $select.empty(); // clear all

                // add default option
                $select.append('<option value="">Select Organisation</option>');

                allOptions.forEach(function (opt) {
                    if (!opt.type) return;

                    if (sourceType === 'External' && opt.type == 5) {
                        $select.append(`<option value="${opt.value}">${opt.text}</option>`);
                    }

                    if (sourceType === 'Internal' && opt.type != 5) {
                        $select.append(`<option value="${opt.value}">${opt.text}</option>`);
                    }
                });

                // refresh select2
                $select.val('').trigger('change');
            }

            $('select[name="exam_source_type"]').on('change', filterOrganisations);

            // initial load
            filterOrganisations();
        });
    </script>
@endpush