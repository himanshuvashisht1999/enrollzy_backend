@extends('auth.auth')
@section('content')
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-8">
                    <img src="https://cdn.dribbble.com/users/1138875/screenshots/4669703/404_animation.gif" alt="">
                </div>
                <div class="col-lg-4 mt-3 p-4">
                    @if (Auth::guard('admin')->user())
                        <h4>Go back to Dashboard</h4>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Back</a>
                    @else
                        <h4>Go back to Login</h4>
                        <a href="{{ route('login') }}" class="btn btn-primary">Back</a>
                    @endif

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('push_script')
@endsection
