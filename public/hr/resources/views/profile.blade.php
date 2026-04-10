@extends('layouts.app')
@section('content')
   <div class="container-fluid">
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
         <h1 class="h3 mb-0 text-gray-800">UPDATE PROFILE</h1>
      </div>
      <div class="row">
         <div class="col-lg-4">
            <div class="col-xl-12 col-md-6 mb-4">
               <div class="card shadow h-100 py-2 here">
                  <div class="card-body onclick1">
                     <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                           <div class="h5 mb-0 font-weight-bold text-gray-800">Account Settings
                           </div>
                        </div>
                        <div class="col-auto">
                           <i class="fas fa-user-cog fa-2x text-gray-300"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-xl-12 col-md-6 mb-4">
               <div class="card shadow h-100 py-2">
                  <div class="card-body onclick2">
                     <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                           <div class="h5 mb-0 font-weight-bold text-gray-800">Notifications</div>
                        </div>
                        <div class="col-auto">
                           <i class="fas fa-flag fa-2x text-gray-300"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-xl-12 col-md-6 mb-4">
               <div class="card shadow h-100 py-2">
                  <div class="card-body onclick3">
                     <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                           <div class="h5 mb-0 font-weight-bold text-gray-800">Membership Plans
                           </div>
                        </div>
                        <div class="col-auto">
                           <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-xl-12 col-md-6 mb-4">
               <div class="card shadow h-100 py-2">
                  <div class="card-body onclick4">
                     <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                           <div class="h5 mb-0 font-weight-bold text-gray-800">Password & Security
                           </div>
                        </div>
                        <div class="col-auto">
                           <i class="fas fa-user-lock fa-2x text-gray-300"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

         </div>

         <div class="col-lg-8">
            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
               </div>
               <div class="card-body d-flex">
                  <a class="nav-link" href="#" id="" role="button" aria-haspopup="true"
                     aria-expanded="false">
                     <img class="img-profile rounded-circle" style="width:6rem" ; src="img/undraw_profile.svg">
                     <span class="ml-3 d-none d-lg-inline text-gray-800 large" style="font-size: 35px;">Douglas
                        McGee</span>
                     <a class="nav-link" href="#" class="btn btn-primary btn-icon-split btn-sm"
                        style="height:30px; margin-top: 35px;">
                        <span class="icon text-white-50">
                           <i class="fas fa-flag"></i>
                        </span>
                        <span class="text">Upload Profile Picture</span>
                     </a>
                  </a>
               </div>
            </div>

            <div class="card shadow mb-4 " id="box1">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Change User Information Here !!</h6>
               </div>
               <div class="p-5">
                  <form class="user">
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="First Name">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="Last Name">
                        </div>
                     </div>
                     <div class="form-group">
                        <input type="email" class="form-control form-control-user" placeholder="Email Address">
                     </div>
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="City">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="State Province">
                        </div>
                     </div>
                     <div class="form-group">
                        <input type="email" class="form-control form-control-user" placeholder="Address">
                     </div>
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="Zip Code">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="Country">
                        </div>
                     </div>

                     <a href="login.html" class="btn btn-primary btn-user btn-block">
                        UPDATE INFORMATION
                     </a>
                     <hr>
                  </form>
               </div>
            </div>
            <div class="card shadow mb-4 d-none " id="box2">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Change User Information Here !!</h6>
               </div>
               <div class="p-5">
                  <form class="user">
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="Firdfgdfst Name">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="Last Name">
                        </div>
                     </div>
                     <div class="form-group">
                        <input type="email" class="form-control form-control-user" placeholder="Email Address">
                     </div>
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="City">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="State Province">
                        </div>
                     </div>
                     <div class="form-group">
                        <input type="email" class="form-control form-control-user" placeholder="Address">
                     </div>
                     <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                           <input type="text" class="form-control form-control-user" placeholder="Zip Code">
                        </div>
                        <div class="col-sm-6">
                           <input type="text" class="form-control form-control-user" placeholder="Country">
                        </div>
                     </div>

                     <a href="login.html" class="btn btn-primary btn-user btn-block">
                        UPDATE INFORMATION
                     </a>
                     <hr>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection
@section('push_script')
   <script>
      $(".onclick1").click(function() {
         $('#box2').addClass('d-none');
         $('#box2').addClass('d-none');
      });
      $(".onclick2").click(function() {

         $('#box1').addClass('d-none');
         $('#box2').removeClass('d-none');
      });
      $(".onclick3").click(function() {
         alert("The paragraph was clicked3.");
      });
      $(".onclick4").click(function() {
         alert("The paragraph was clicked4.");
      });
   </script>
@endsection
