@extends('admin.layouts.master')

@section('title', 'Create Digital Card')

@section('content')
<div class="container-fluid py-2">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-plus-circle text-primary me-2"></i> Create Digital Business Card
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.digital-cards.index') }}">Digital Cards</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.digital-cards.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Cards
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Please correct the following errors:</h6>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.digital-cards.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.digital_cards.partials.form')
    </form>
</div>
@endsection
