<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="">
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
    <!-- for live demo page -->
    @yield('push_css')
    <style>
        .cke_notification.cke_notification_warning{
            display:none;
        }
        #holder img {
            border: 1px solid red;
            border-radius: 10px;
            margin: 10px;
        }

        .zoom {
            transition: transform .2s;
            margin: 0 auto;
        }

        .zoom:hover {
            transform: scale(1.5);
            /* (150% zoom - Note: if the zoom is too large, it will go outside of the viewport) */
        }

        @font-face {
            font-family: myfont;
            src: url(Roboto-Regular.woff);
        }

        #Loaading {
            display: none;
            position: fixed;
            top: 0px;
            left: 0px;
            width: 0px;
            height: 5px;
            margin: 0px;
            background: #002fff;
            animation: loader 20s;
            animation-fill-mode: both;
            -webkit-user-select: none;
            z-index: 9999;
        }

        #Loaading.loader {
            display: block !important;
            /* Show loader when `.loader` class is added */
        }

        @keyframes loader {
            0% {
                width: 0%;
            }

            25% {
                width: 22%;
            }

            50% {
                width: 55%;
            }

            75% {
                width: 83%;
            }

            100% {
                width: 100%;
            }

        }

        .confetti-button {
            position: relative;
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="" id="Loaading"></div>
    <div id="wrapper">
        <!-- Sidebar -->
        @if (!Route::is('admin.sales.create') && !Route::is('admin.forms.create') && !Route::is('admin.sales.view_order_detail'))
            @include('layouts.sidebar')
        @endif
        <!-- End of Sidebar -->
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                @include('layouts.header')
                <!-- End of Topbar -->
                <!-- Begin Page Content -->
                @yield('content')
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            @if (!Route::is('admin.sales.create')&& !Route::is('admin.forms.create') && !Route::is('admin.sales.view_order_detail'))
                <!-- Footer -->
                <footer class="sticky-footer bg-white">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>Copyright &copy; Your Website 2021</span>
                        </div>
                    </div>
                </footer>
                <!-- End of Footer -->
            @endif
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="{{ URL::asset('admin/vendor/jquery/jquery.js') }}"></script>
    <script src="{{ URL::asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::asset('admin/js/sb-admin-2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.2/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <script>
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: false,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
        };
        // ------------toastr End here------------
    </script>

    <script>
        $(document).ready(function() {
            $(document).ajaxComplete(function(event, xhr) {
                const errorMessageHeader = xhr.getResponseHeader('X-Error-Message');
                if (errorMessageHeader) {
                    toastr.error(errorMessageHeader);
                }
            });
        });
        @if (session('success'))
            $(document).ready(function() {
                toastr["success"]("{{ session('success') }}", 'Success!');
            });
        @elseif (session('error'))
            $(document).ready(function() {
                toastr["error"]("{{ session('error') }}", 'Error!');
            });
        @endif
    </script>
    <script>
        $body = $("#Loaading");
        $(document).on({
            ajaxStart: function() {
                $body.addClass("loader");
            },
            ajaxStop: function() {
                $body.removeClass("loader");
            }
        });
    </script>
    @yield('push_script')
    @stack('push_script')
    <script>
        function CongratulationFire() {
            var count = 500;
            var defaults = {
                origin: {
                    x: 0.9,
                    y: 0.9
                } // Set origin to top-right corner
            };

            function fire(particleRatio, opts) {
                confetti(Object.assign({}, defaults, opts, {
                    particleCount: Math.floor(count * particleRatio)
                }));
            }

            fire(0.25, {
                spread: 26,
                startVelocity: 55,
            });
            fire(0.2, {
                spread: 60,
            });
            fire(0.35, {
                spread: 100,
                decay: 0.91,
                scalar: 0.8
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 25,
                decay: 0.92,
                scalar: 1.2
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 45,
            });

        }
    </script>
    @if (session('sucongratulation'))
        <script>
            toastr["success"]("{{ session('sucongratulation') }}", 'Success!');
            CongratulationFire();
        </script>
    @endif
</body>

</html>
