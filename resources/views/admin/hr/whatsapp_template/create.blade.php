@extends('admin.layouts.master')

@section('title', 'New WhatsApp Template')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 800px;">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Create Marketing Template</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hr.whatsapp_template.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g., Welcome Message, Festival Promo" required>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label small fw-bold">Message Content <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control rounded-3" rows="8" placeholder="Enter your WhatsApp message here..." required></textarea>
                        <div class="form-text mt-1 small">Use text formatting like *bold*, _italic_, or ~strikethrough~.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Default Image Caption</label>
                        <input type="text" name="caption" class="form-control rounded-3" placeholder="Caption for media attachments">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.hr.whatsapp_template.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
