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
                           <h1 class="h4 text-gray-900 mb-4">Recover your Password!</h1>
                        </div>
                        <form class="user" method="POST" action="{{ route('checkAccount') }}" id="resetpassword_form">
                           @csrf
                           <div class="form-group">
                              <input type="text" class="form-control form-control-user"
                                 placeholder="Enter Email or Mobile Number" name="email_or_mobile"
                                 value="{{ old('email_or_mobile') }}">
                           </div>
                           <button type="submit" class="btn btn-primary btn-user btn-block" id="login_btn"> Find Account
                           </button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn-primary btn-sm"> Login</a>
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
