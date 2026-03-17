@extends('admin.layouts.auth')

@section('content')
<section class="login-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">

            <!-- LEFT IMAGE -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <div class="login-illustration floating">
                    <img 
                        src="{{ asset('images/auth/login-password.png') }}" 
                        alt="Admin Login" >
                </div>
            </div>

            <!-- RIGHT FORM -->
            <div class="col-lg-6">
                <div class="login-card admin-login-card text-center shadow-lg p-5 bg-white rounded">
                    <span class="admin-badge">ADMINISTRATION</span>
                    <h2 class="mb-4">Secure Admin Login</h2>

                    <form action="{{ route('login') }}" method="POST" id="adminLoginForm">
                        @csrf

                        @if(session('error'))
                            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                        @endif

                        <!-- EMAIL -->
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                placeholder="Admin Email"
                                required>
                        </div>
                        @error('email')
                            <div class="text-danger mb-2 text-start small">{{ $message }}</div>
                        @enderror

                        <!-- PASSWORD -->
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Password"
                                required>
                        </div>
                        @error('password')
                            <div class="text-danger mb-2 text-start small">{{ $message }}</div>
                        @enderror

                        <button type="submit" class="btn btn-danger w-100 mb-3">
                            Login as Admin
                        </button>

                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

@push('js')
<script>
    document.getElementById('adminLoginForm').addEventListener('submit', function() {
        console.log('Admin login form submitted!');
    });
</script>
@endpush
@endsection
