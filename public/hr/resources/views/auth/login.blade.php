@extends('auth.auth')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-auth-image"></div>
                        <div class="col-lg-6 align-self-center">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                </div>
                                <form class="user" method="POST" action="{{ route('checkCredentials') }}"
                                    id="login_form">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-user"
                                            placeholder="Enter Username..." name="username" value="{{ old('username') }}">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" placeholder="Password"
                                            autocomplete="disabled" name="password" value="{{ old('password') }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck">Remember
                                                Me</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block" id="login_btn"> Login
                                    </button>
                                    <a class="btn btn-dark btn-user btn-block" href="{{ route('passwordReset') }}"> Forgot
                                        Password
                                    </a>
                                </form>
                                <hr>
                                <div class="text-center">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
@endsection
