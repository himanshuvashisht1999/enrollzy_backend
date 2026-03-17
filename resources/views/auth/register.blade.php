@extends('layouts.master')

@push('css')
<link rel="stylesheet" href="{{ asset('css/login/register.css') }}">
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
@endpush

@section('content')
<section class="register-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">

            <!-- LEFT ILLUSTRATION -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <div class="register-illustration floating">
                    <img src="{{ asset('images/auth/register.png') }}" alt="Register">
                </div>
            </div>

            <!-- RIGHT FORM -->
            <div class="col-lg-6">
                <div class="register-card">

                    <h2 class="text-center mb-4">Create New Account</h2>

                    <form action="{{ route('register.submit') }}" method="POST">
                        @csrf

                        <!-- Full Name -->
                        <div class="mb-3">
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Full Name"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mobile -->
                        <div class="mb-3">
                            <div class="mobile-group">
                                <span class="country-code">+91</span>
                                <input
                                    type="tel"
                                    name="mobile"
                                    class="form-control @error('mobile') is-invalid @enderror"
                                    placeholder="Mobile Number"
                                    value="{{ old('mobile') }}"
                                    required
                                >
                            </div>
                            @error('mobile')
                                <div class="text-danger mt-1" style="font-size: 0.875em;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Password"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Confirm Password"
                                required
                            >
                        </div>
                        
                        <!-- Pincode -->
                        {{-- <input type="text" class="form-control" placeholder="Pin Code"> --}}
                        
                        <!-- Address Row -->
                        {{-- 
                        <div class="row g-2">
                             ... Address fields kept commented or removed if not needed for initial registration ...
                        </div> 
                        --}}

                        <!-- Terms -->
                        <div class="form-check mt-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="terms"
                                required
                            >
                            <label class="form-check-label" for="terms">
                                I agree with <a href="#">Terms</a> and <a href="#">Privacy</a>
                            </label>
                        </div>

                        <!-- Submit -->
                        <div id="registerActions">
                            <button type="button" id="sendOtpBtn" class="btn btn-theme-one w-100 mt-2">
                                Send OTP & Sign Up
                            </button>
                        </div>

                        <!-- OTP Verification Section (Hidden initially) -->
                        <div id="otpSection" style="display: none;">
                            <div class="divider">Verify OTP</div>
                            <div class="mb-3">
                                <input type="text" id="otpInput" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6">
                            </div>
                            <button type="button" id="verifyOtpBtn" class="btn btn-success w-100">
                                Verify & Complete Registration
                            </button>
                            <div class="text-center mt-2">
                                <a href="javascript:void(0)" id="changeDetailsBtn" class="text-muted small">Change Details</a>
                            </div>
                        </div>

                        <div id="recaptcha-container"></div>


                        <!-- Footer -->
                        <p class="register-footer mt-2">
                            Already have an account?
                            <a href="{{ route('login') }}">Sign In</a>
                        </p>

                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
    const firebaseConfig = {
        apiKey: "AIzaSyDISZTQSab0rRd8ARiadaQgokmCRwgbP-A",
        authDomain: "enrollzy.firebaseapp.com",
        projectId: "enrollzy",
        storageBucket: "enrollzy.firebasestorage.app",
        messagingSenderId: "323478887040",
        appId: "1:323478887040:web:c8631fc5981e15ac37a465",
        measurementId: "G-YWST0ZBBME"
    };

    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();

    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible'
    });

    document.getElementById('sendOtpBtn').addEventListener('click', function() {
        const name = document.querySelector('input[name="name"]').value;
        const mobile = document.querySelector('input[name="mobile"]').value;
        const password = document.querySelector('input[name="password"]').value;
        const password_confirmation = document.querySelector('input[name="password_confirmation"]').value;

        if (!name || mobile.length !== 10 || !password || password !== password_confirmation) {
            alert("Please fill all fields correctly. Passwords must match.");
            return;
        }

        const phoneNumber = "+91" + mobile;
        const appVerifier = window.recaptchaVerifier;

        document.getElementById('sendOtpBtn').disabled = true;
        document.getElementById('sendOtpBtn').innerText = "Sending OTP...";

        auth.signInWithPhoneNumber(phoneNumber, appVerifier)
            .then((confirmationResult) => {
                window.confirmationResult = confirmationResult;
                document.getElementById('registerActions').style.display = 'none';
                document.getElementById('otpSection').style.display = 'block';
                
                // Optional: Store registration data in session via hidden form or AJAX if needed
                // But we'll just submit it all at once at the end.
            }).catch((error) => {
                alert("Error: " + error.message);
                document.getElementById('sendOtpBtn').disabled = false;
                document.getElementById('sendOtpBtn').innerText = "Send OTP & Sign Up";
            });
    });

    document.getElementById('verifyOtpBtn').addEventListener('click', function() {
        const code = document.getElementById('otpInput').value;
        const formData = new FormData(document.querySelector('form'));
        
        document.getElementById('verifyOtpBtn').disabled = true;
        document.getElementById('verifyOtpBtn').innerText = "Verifying...";

        window.confirmationResult.confirm(code).then((result) => {
            const user = result.user;
            
            // Add firebase token and otp to form data
            formData.append('firebase_token', user.accessToken);
            formData.append('otp', code);

            fetch("{{ route('register.submit') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = data.redirect;
                  } else {
                      alert(data.message || "Registration failed");
                      document.getElementById('verifyOtpBtn').disabled = false;
                      document.getElementById('verifyOtpBtn').innerText = "Verify & Complete Registration";
                  }
              });
        }).catch((error) => {
            alert("Invalid OTP: " + error.message);
            document.getElementById('verifyOtpBtn').disabled = false;
            document.getElementById('verifyOtpBtn').innerText = "Verify & Complete Registration";
        });
    });

    document.getElementById('changeDetailsBtn').addEventListener('click', function() {
        document.getElementById('registerActions').style.display = 'block';
        document.getElementById('otpSection').style.display = 'none';
        document.getElementById('sendOtpBtn').disabled = false;
        document.getElementById('sendOtpBtn').innerText = "Send OTP & Sign Up";
    });
</script>
@endsection
