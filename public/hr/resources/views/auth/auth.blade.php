<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <meta name="description" content="">
   <meta name="robots" content="noindex, nofollow">
   <meta name="author" content="">
   <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
   <title>Admin Panel</title>
   <!-- Custom fonts for this template-->
   <link href="{{ URL::asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
   <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
   <!-- Custom styles for this template-->
   <link href="{{ URL::asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
   <link href="{{ URL::asset('admin/css/app.css') }}" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
   <div class="container">
      @yield('content')

   </div>
   <!-- Bootstrap core JavaScript-->
   <script src="{{ URL::asset('admin/vendor/jquery/jquery.min.js') }}"></script>
   <script src="{{ URL::asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
   <script src="{{ URL::asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
   <script src="{{ URL::asset('admin/js/sb-admin-2.min.js') }}"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.2/js/toastr.min.js"></script>

   <script>
      toastr.options = {
         "closeButton": true,
         "debug": false,
         "newestOnTop": true,
         "progressBar": false,
         "positionClass": "toast-top-right",
         "preventDuplicates": false,
         "onclick": null,
         "showDuration": "300",
         "hideDuration": "1000",
         "timeOut": "5000",
         "extendedTimeOut": "1000",
         "showEasing": "swing",
         "hideEasing": "linear",
         "showMethod": "fadeIn",
         "hideMethod": "fadeOut"
      };
      // ------------toastr End here------------
      @if (session('success'))
         $(document).ready(function() {
            toastr["success"]("{{ session('success') }}", 'Success!');
         });
      @elseif (session('error'))
         $(document).ready(function() {
            toastr["error"]("{{ session('error') }}", 'Error!');
         });
      @endif
      // ------------------------jquery end here------------------------
   </script>
   @yield('push_script')
</body>

</html>
