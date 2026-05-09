@extends('admin.layouts.master')

@section('title', 'Add New Customer')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .dynamic-fields-section { border-top: 1px solid #eee; margin-top: 2rem; padding-top: 1.5rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Customer Profile Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.customers.index.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Contact Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Customer Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select select2 rounded-3" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Sub-Category</label>
                        <select name="sub_category_id" id="sub_category_id" class="form-select select2 rounded-3">
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Institute</label>
                        <select name="institute_id" class="form-select select2 rounded-3">
                            <option value="">No Institute</option>
                            @foreach($institutes as $ins)
                                <option value="{{ $ins->id }}">{{ $ins->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control rounded-3" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">State</label>
                        <input type="text" name="state" class="form-control rounded-3" value="{{ old('state') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Country</label>
                        <input type="text" name="country" class="form-control rounded-3" value="{{ old('country', 'India') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    @if($fields->count() > 0)
                    <div class="col-12 dynamic-fields-section">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Additional Information</h6>
                        <div class="row g-3">
                            @foreach($fields as $field)
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">{{ $field->label }} @if($field->is_required) <span class="text-danger">*</span> @endif</label>
                                <input type="text" name="customer_fields[{{ $field->id }}]" class="form-control rounded-3" @if($field->is_required) required @endif>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.customers.index.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $('#category_id').on('change', function() {
            let pid = $(this).val();
            if(!pid) return;

            $.post("{{ route('admin.hr.customers.get-sub-categories') }}", {
                parent_id: pid,
                _token: "{{ csrf_token() }}"
            }, function(res) {
                let sub = $('#sub_category_id');
                sub.empty().append('<option value="">No Sub-Category</option>');
                $.each(res.data, function(k, v) {
                    sub.append(`<option value="${v.id}">${v.name}</option>`);
                });
            });
        });
    });
</script>
@endpush
