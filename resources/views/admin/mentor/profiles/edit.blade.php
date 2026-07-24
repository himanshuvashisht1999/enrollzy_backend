@extends('admin.layouts.master')

@section('title', 'Edit Mentor Profile')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.mentor.profiles.index') }}" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to All Mentors
        </a>
        <h3 class="fw-bold mt-2">Edit Mentor: {{ $profile->first_name }} {{ $profile->last_name }}</h3>
    </div>

    <div class="row">
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.mentor.profiles.update', $profile->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name</label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $profile->first_name) }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $profile->last_name) }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Professional Headline</label>
                                <input type="text" name="professional_headline" class="form-control @error('professional_headline') is-invalid @enderror" value="{{ old('professional_headline', $profile->professional_headline) }}">
                                @error('professional_headline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Price Per Minute (₹)</label>
                                <input type="number" name="price_per_min" class="form-control @error('price_per_min') is-invalid @enderror" value="{{ old('price_per_min', $profile->price_per_min ?? 500) }}">
                                @error('price_per_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">City</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $profile->city) }}">
                                @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">State / Country</label>
                                <input type="text" name="state_country" class="form-control @error('state_country') is-invalid @enderror" value="{{ old('state_country', $profile->state_country) }}">
                                @error('state_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Profile Photo</label>
                                @if(!empty($profile->profile_photo))
                                    @php
                                        $photoUrl = str_starts_with($profile->profile_photo, 'http') ? $profile->profile_photo : asset('storage/' . $profile->profile_photo);
                                    @endphp
                                    <div class="mb-2">
                                        <img src="{{ $photoUrl }}" alt="Current Photo" class="rounded-circle border p-1" style="width: 60px; height: 60px; object-fit: cover;" onError="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Mentor&background=4e73df&color=ffffff';">
                                    </div>
                                @endif
                                <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                                @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Short Bio / About Mentor</label>
                                <textarea name="short_bio" class="form-control @error('short_bio') is-invalid @enderror" rows="4">{{ old('short_bio', $profile->short_bio) }}</textarea>
                                @error('short_bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                    <i class="fas fa-save me-1"></i> Update Mentor Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
