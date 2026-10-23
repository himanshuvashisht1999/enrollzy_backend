@extends('admin.layouts.master')

@section('title', 'Edit Digital Card - ' . $card->name)

@section('content')
<div class="container-fluid py-2">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-edit text-primary me-2"></i> Edit Digital Card: <span class="text-primary">{{ $card->name }}</span>
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.digital-cards.index') }}">Digital Cards</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $card->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $card->public_url }}" target="_blank" class="btn btn-outline-info rounded-3">
                <i class="fas fa-external-link-alt me-1"></i> View Live Card
            </a>
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

    <form action="{{ route('admin.digital-cards.update', $card->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.digital_cards.partials.form')
    </form>
</div>
@endsection
