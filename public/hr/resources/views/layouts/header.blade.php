@php
    $user_id = Auth::guard('admin')->user()->organization_id;
    $qrUrl = 'https://hrzenix.com/admin/whatsapp-qr/qr_org_'.$user_id.'.png';
    $hasQr = false;

    $headers = @get_headers($qrUrl, 1);
    if ($headers && isset($headers[0]) && str_contains($headers[0], '200') && isset($headers['Content-Type']) && str_contains($headers['Content-Type'], 'image')) {
        $hasQr = true;
    }
@endphp

@if ($hasQr)
    <div style="background-color: #dc3545; color: white; text-align: center; padding: 10px; font-weight: bold;">
        ⚠️ WhatsApp message is stopped —
        <a href="{{ $qrUrl }}" target="_blank" style="color: #fff; text-decoration: underline;">
            Click here to scan QR
        </a>.
    </div>
@endif
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"> <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    @php
        use Carbon\Carbon;
        $attendance = App\Models\Attendance::where('date', date('Y-m-d'))
            ->whereNull('check_out')
            ->where('staff_id', Auth::guard('admin')->id())
            ->first();
        // Calculate duration if attendance is found
        if ($attendance) {
            $checkInTime = Carbon::parse($attendance->check_in);
            $currentTime = Carbon::now();
            $currentTime = Carbon::now();
            // Calculate the difference in hours, minutes, and seconds
            $duration = $checkInTime->diff($currentTime);
            // Format the duration as H:i:s
            $formattedDuration = $duration->format('%H hours, %I minutes, %S seconds');
        }
    @endphp
    <style>
        .launch-animation {
            display: inline-block;
            animation: launch 1s infinite alternate;
            /* Infinite animation alternating */
        }

        @keyframes launch {
            0% {
                transform: translateX(0);
                /* Start at original position */
                opacity: 0.7;
                /* Initial opacity */
            }

            50% {
                transform: translateX(5px);
                /* Move forward */
                opacity: 1;
                /* Fully visible */
            }

            100% {
                transform: translateX(0);
                /* Move back to original position */
                opacity: 0.7;
                /* Initial opacity */
            }
        }
    </style>
    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mx-1" style="align-self: center">
            <a href="{{ route('admin.clear-cache') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-fighter-jet launch-animation"> Admin Cache</i>
            </a>
        </li>
        <!-- <li class="nav-item mx-1" style="align-self: center">
            <a href="{{ env('WEB_URL') }}/api/clear-cache" class="btn btn-sm btn-primary">
                <i class="fa fa-fighter-jet launch-animation"> Frontend Cache</i>
            </a>
        </li> -->
        @if ($attendance)
            {{-- worked time --}}
            <li class="nav-item mx-1" style="align-self: center">
                <span class="btn btn-sm btn-primary">
                    Worked: {{ $formattedDuration }}
                </span>
            </li>
            {{--  worked time --}}
        @endif
        {{-- Notification Center --}}
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-danger badge-counter">3+</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2019</div>
                        <span class="font-weight-bold">A new monthly report is ready to
                            download!</span>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All
                    Alerts</a>
            </div>
        </li>
        {{-- Notification Center --}} <!-- Nav Item - Messages Box-->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-danger badge-counter">7</span>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle"
                            src="https://static.vecteezy.com/system/resources/thumbnails/009/636/683/small_2x/admin-3d-illustration-icon-png.png"
                            alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                            problem I've been having.</div>
                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle"
                            src="https://static.vecteezy.com/system/resources/thumbnails/009/636/683/small_2x/admin-3d-illustration-icon-png.png"
                            alt="...">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I have the photos that you ordered last month, how
                            would you like them sent to you?</div>
                        <div class="small text-gray-500">Jae Chun · 1d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle"
                            src="https://static.vecteezy.com/system/resources/thumbnails/009/636/683/small_2x/admin-3d-illustration-icon-png.png"
                            alt="...">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great, I am very happy
                            with
                            the progress so far, keep up the good work!</div>
                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle"
                            src="https://static.vecteezy.com/system/resources/thumbnails/009/636/683/small_2x/admin-3d-illustration-icon-png.png"
                            alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                            told me that people say this to all dogs, even if they aren't good...</div>
                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More
                    Messages</a>
            </div>
        </li>
        <!-- Nav Item - Messages Box-->
        <div class="topbar-divider d-none d-sm-block"></div> <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span
                    class="mr-2 d-none d-lg-inline text-gray-600  text-capitalize">{{ Auth::user()->username }}</span>
                <img class="img-profile rounded-circle"
                    src="https://static.vecteezy.com/system/resources/thumbnails/009/636/683/small_2x/admin-3d-illustration-icon-png.png">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize" id="exampleModalLabel">Hi {{ Auth::user()->username }}, Are
                    you ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="{{ route('logout') }}">Logout</a>
            </div>
        </div>
    </div>
</div>
