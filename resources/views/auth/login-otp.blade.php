@extends('layouts.master')

@push('css')
<link rel="stylesheet" href="{{ asset('css/login/login-otp.css') }}">
<!-- Firebase App (the core Firebase SDK) is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<!-- Add Firebase products that you want to use -->
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
@endpush
@section('content')



<section class="login-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">

            <!-- LEFT IMAGE -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <div class="login-illustration floating">
                    <img 
                        src="{{ asset('images/auth/login-otp.png') }}" 
                        alt="Login Illustration">
                </div>
            </div>

            <!-- RIGHT FORM -->
            <div class="col-lg-6">
                <div class="login-card">

                    <h2 class="mb-4 text-center">Login Your Account</h2>

                    <!-- OTP FORM -->
                    <form id="loginForm">
                        @csrf
                        <!-- MOBILE INPUT -->
                        <div id="phoneInputSection">
                            <div class="otp-input-group mb-4">
                                <span class="country-code">+91</span>
                                <input type="tel" id="mobileNumber" name="mobile" placeholder="Enter mobile number" maxlength="10" required>
                            </div>

                            <div id="recaptcha-container"></div>

                            <button type="button" id="sendOtpBtn" class="btn btn-theme-one w-100 mb-3">
                                Send OTP
                            </button>
                        </div>

                        <!-- OTP INPUT (Hidden initially) -->
                        <div id="otpInputSection" style="display: none;">
                            <div class="mb-4 text-center">
                                <p class="text-muted">Enter the 6-digit code sent to <strong id="displayMobile"></strong></p>
                            </div>
                            <div class="otp-input-group mb-4">
                                <span class="country-code"><i class="fas fa-key"></i></span>
                                <input type="text" id="otpInput" name="otp" placeholder="Enter OTP" maxlength="6" required style="letter-spacing: 5px; text-align: center; font-weight: bold; font-size: 1.2rem;">
                            </div>

                            <button type="button" id="verifyOtpBtn" class="btn btn-theme-one w-100 mb-3">
                                Verify & Proceed
                            </button>
                            <div class="text-center">
                                <a href="javascript:void(0)" id="changeMobileBtn" class="text-muted small">Change Mobile Number</a>
                            </div>
                        </div>
                    </form>

                    <div class="divider">or</div>

                    <div class="alt-login">
                        <a href="{{route('login')}}" class="alt-btn text-center password w-100">
                            🔐 Using Password
                        </a>
                    </div>

                    <p class="signup-text mt-4 text-center">
                        Don’t have an account?
                        <a href="{{route('register')}}" class="fw-bold">Sign Up</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
    // Your web app's Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyDISZTQSab0rRd8ARiadaQgokmCRwgbP-A",
        authDomain: "enrollzy.firebaseapp.com",
        projectId: "enrollzy",
        storageBucket: "enrollzy.firebasestorage.app",
        messagingSenderId: "323478887040",
        appId: "1:323478887040:web:c8631fc5981e15ac37a465",
        measurementId: "G-YWST0ZBBME"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    const auth = firebase.auth();
    auth.languageCode = 'en';

    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible',
        'callback': (response) => {
            // reCAPTCHA solved, allow signInWithPhoneNumber.
        }
    });

    document.getElementById('sendOtpBtn').addEventListener('click', function() {
        const mobile = document.getElementById('mobileNumber').value;
        if (mobile.length !== 10) {
            alert("Please enter a valid 10-digit mobile number.");
            return;
        }
        const phoneNumber = "+91" + mobile;
        const appVerifier = window.recaptchaVerifier;

        document.getElementById('sendOtpBtn').disabled = true;
        document.getElementById('sendOtpBtn').innerText = "Sending...";

        auth.signInWithPhoneNumber(phoneNumber, appVerifier)
            .then((confirmationResult) => {
                window.confirmationResult = confirmationResult;
                
                document.getElementById('phoneInputSection').style.display = 'none';
                document.getElementById('otpInputSection').style.display = 'block';
                document.getElementById('displayMobile').innerText = phoneNumber;
                
                // Keep track in session via AJAX for backend logic
                fetch("{{ route('login.otp.submit') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        mobile: mobile,
                        firebase_flow: true
                    })
                });

            }).catch((error) => {
                console.error("Error during signInWithPhoneNumber", error);
                alert("Error sending OTP: " + error.message);
                document.getElementById('sendOtpBtn').disabled = false;
                document.getElementById('sendOtpBtn').innerText = "Send OTP";
            });
    });

    document.getElementById('verifyOtpBtn').addEventListener('click', function() {
        const code = document.getElementById('otpInput').value;
        const mobile = document.getElementById('mobileNumber').value;

        document.getElementById('verifyOtpBtn').disabled = true;
        document.getElementById('verifyOtpBtn').innerText = "Verifying...";

        window.confirmationResult.confirm(code).then((result) => {
            // User signed in successfully.
            const user = result.user;
            
            // Now tell our Laravel backend to log this user in
            fetch("{{ route('otp.verify.submit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    otp: code, // We pass it for standard validation if needed, but Firebase already verified
                    firebase_token: user.accessToken,
                    mobile: mobile
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = data.redirect;
                  } else {
                      alert(data.message || "Login failed");
                      document.getElementById('verifyOtpBtn').disabled = false;
                      document.getElementById('verifyOtpBtn').innerText = "Verify & Proceed";
                  }
              });

        }).catch((error) => {
            alert("Invalid OTP: " + error.message);
            document.getElementById('verifyOtpBtn').disabled = false;
            document.getElementById('verifyOtpBtn').innerText = "Verify & Proceed";
        });
    });

    document.getElementById('changeMobileBtn').addEventListener('click', function() {
        document.getElementById('phoneInputSection').style.display = 'block';
        document.getElementById('otpInputSection').style.display = 'none';
        document.getElementById('sendOtpBtn').disabled = false;
        document.getElementById('sendOtpBtn').innerText = "Send OTP";
    });
</script>
@endsection
