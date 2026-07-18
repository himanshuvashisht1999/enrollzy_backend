@extends('admin.layouts.master')

@section('title', 'Add FAQ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add FAQ</h4>
    <a href="{{ route('admin.faq-items.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.faq-items.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="faq_category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('faq_category_id') is-invalid @enderror" id="faq_category_id" name="faq_category_id" required>
                                <option value="" disabled selected>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('faq_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('faq_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                            <small class="text-muted">Lower numbers appear first.</small>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="question" class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('question') is-invalid @enderror" id="question" name="question" value="{{ old('question') }}" required>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="answer" class="form-label fw-bold">Answer <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('answer') is-invalid @enderror" id="answer" name="answer" rows="5" required>{{ old('answer') }}</textarea>
                        @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" checked value="1">
                        <label class="form-check-label" for="status">Active Status</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">Save FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
