@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Dashboard</h1>
            <div>
                @php
                    $cameraAction = $attendance ? 'check_out' : 'check_in';
                @endphp
                <button class="btn btn-info btn-lg"
                        id="cameraPunchBtn"
                        data-toggle="modal"
                        data-target="#cameraPunchModal"
                        data-action="{{ $cameraAction }}"
                        data-checkin-url="{{ route('admin.clock.check_in') }}"
                        data-checkout-url="{{ route('admin.clock.check_out') }}">
                    <i class="fas fa-camera fa-sm"></i> {{$cameraAction == 'check_out' ? 'Camera Punch Out' : 'Camera Punch In' }}
                </button>

                <!-- Camera Modal -->
                <div class="modal fade" id="cameraPunchModal" tabindex="-1" role="dialog" aria-labelledby="cameraPunchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-camera"></i> Face verification</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <video id="cameraVideo" autoplay muted playsinline style="width:100%; max-height:360px; background:#000;"></video>
                        <canvas id="captureCanvas" style="display:none;"></canvas>

                        <div id="cameraStatus" class="mt-2 text-muted">Initializing camera...</div>

                        <div class="mt-3">
                        <button id="captureFaceBtn" class="btn btn-primary">Capture & Verify</button>
                        <button id="closeCameraBtn" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>

                        <div id="verificationResult" class="mt-3"></div>
                    </div>
                    </div>
                </div>
                </div>
                {{-- ......................... --}}
                @if ($attendance)
                    @php
                        // Fetch the latest break entry for the staff and attendance
                        $breaks = App\Models\Breaks::where('attendance_id', $attendance->id)
                            ->where('staff_id', Auth::guard('admin')->id())
                            ->latest('created_at')
                            ->first();
                    @endphp
                    @if ($breaks)
                        @if ($breaks->end === null && $breaks->duration === null)
                            <!-- Ongoing break -->
                            @if ($breaks->type == 'lunch')
                                <a class="btn btn-warning btn-lg" href="javascript:;" data-toggle="modal"
                                    data-target="#endLunchBreak">
                                    <i class="fas fa-pause fa-sm text-white-50"></i> End Lunch Break
                                </a>
                            @elseif ($breaks->type == 'personal')
                                <a class="btn btn-warning btn-lg" href="javascript:;" data-toggle="modal"
                                    data-target="#endLunchBreak">
                                    <i class="fas fa-pause fa-sm text-white-50"></i> Return to Work
                                </a>
                            @endif
                        @else
                            <!-- Not an ongoing break, offer the option to start a new break -->
                            <a class="btn btn-warning btn-lg" href="javascript:;" data-toggle="modal"
                                data-target="#breakTimeModal">
                                <i class="fas fa-pause fa-sm text-white-50"></i> Take a Break
                            </a>
                        @endif
                    @else
                        <!-- No breaks yet, offer the option to start a new break -->
                        <a class="btn btn-warning btn-lg" href="javascript:;" data-toggle="modal"
                            data-target="#breakTimeModal">
                            <i class="fas fa-pause fa-sm text-white-50"></i> Take a Break
                        </a>
                    @endif
                    <a class="btn btn-danger btn-lg" href="javascript:;" data-toggle="modal" data-target="#logoutDaymodal">
                        <i class="fas fa-sign-out-alt fa-sm text-white-50"></i> Punch Out
                    </a>
                @else
                    <a class="btn btn-success btn-lg" href="javascript:;" data-toggle="modal" data-target="#checkInModal">
                        <i class="fas fa-sign-in-alt fa-sm text-primary"></i> Punch IN
                    </a>
                @endif
                <!-- <a class="btn btn-primary btn-sm" href="javascript:;" data-toggle="modal" data-target="#Generate_Report">
                    <i class="fas fa-download fa-sm text-white-50">
                    </i> Generate Report
                </a> -->
            </div>
        </div>
        <!-- check in modal -->
        @include('modal.checkInModal')
        @if ($attendance)
            <!-- Start break  modal -->
            @include('modal.breakTimeModal')
            <!-- end lunch break modal -->
            @include('modal.endBreakModal')
            <!-- logout Day modal -->
            @include('modal.logoutDaymodal')
        @endif
        <!-- export csv modal -->
        @include('modal.exportmodal')
        <!-- Content Row -->
        <div class="row">
            
            @php
                use App\Models\Tasks; 
                use App\Models\MasterOrder;
                $authuser = Auth::user()->id;
                
                $TasksData = Tasks::where('assigned_to', $authuser)->where('status', '!=', 'completed')->get();
             @endphp
             @if($TasksData->isNotEmpty())
            <div class="col-xl-12 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"> You have Pending Tasks
                                </div>
                                @foreach($TasksData as $data)
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Please complete pending Task - <a href="{{(route('admin.task.edit', encrypt($data->id)))}}" target="_blank">{{$data->title}}</a></div>
                                @endforeach
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300">
                                </i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Pending Requests Card Example -->
            <!-- <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"> Total Staff
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $count['countStaff'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-comments fa-2x text-gray-300">
                                </i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            
        </div>
    </div>
@endsection
@section('push_script')
    <script src="{{ URL::asset('admin/vendor/chart.js/Chart.min.js') }}"></script>
    <script src="{{ URL::asset('admin/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ URL::asset('admin/js/demo/chart-pie-demo.js') }}"></script>
    <script>
        // Reusable function for AJAX requests
        function handleAjaxRequest(buttonId, route, formId, successMessage, errorMessage) {
            let isProcessing = false;

            $(buttonId).click(function(e) {
                e.preventDefault();
                if (!isProcessing) {
                    isProcessing = true;
                    $.ajax({
                        type: 'POST',
                        url: route,
                        data: $(formId).serializeArray(),
                        success: function(response) {
                            if (response.status == 1) {
                                toastr["success"](response.message, "Success");
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                toastr["error"](response.message, "Error");
                            }
                            isProcessing = false;
                        },
                        error: function() {
                            isProcessing = false;
                            toastr["error"](errorMessage, "Error");
                        }
                    });
                } else {
                    toastr["info"](
                        'Sabr na ho rhi ... Jayada jaldi me ho ,Please wait, your request is being processed...',
                        "Hold on"
                    );
                }
            });
        }

        // Initialize AJAX requests for different actions
        handleAjaxRequest(
            "#checkBtn",
            "{{ route('admin.clock.check_in') }}",
            '#checkInForm',
            "Check-in successful.",
            'Something went wrong. Please try again.'
        );

        handleAjaxRequest(
            "#breakBtn",
            "{{ route('admin.clock.start_break') }}",
            '#breakForm',
            "Break started successfully.",
            'Something went wrong. Please try again.'
        );

        handleAjaxRequest(
            "#endBreakBtn",
            "{{ route('admin.clock.end_lunchBreak') }}",
            '#endLunchForm',
            "Break ended successfully.",
            'Something went wrong. Please try again.'
        );
        handleAjaxRequest(
            "#logoutBtn",
            "{{ route('admin.clock.check_out') }}",
            '#endDayForm',
            "Good Bye Dear, will meet soon.",
            'Something went wrong. Please try again.'
        );
    </script>

<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    window.referenceImages = @json($referenceImages ?? []);
</script>
<script>
$(function() {
  const modelPath = '/models';
  const MATCH_THRESHOLD = 0.55; // tune this (lower = stricter)
  const AUTO_INTERVAL_MS = 900; // how often to attempt capture + verify

  const $modal = $('#cameraPunchModal');
  const video = document.getElementById('cameraVideo');
  const canvas = document.getElementById('captureCanvas');
  const $status = $('#cameraStatus');
  const $result = $('#verificationResult');
  const $captureBtn = $('#captureFaceBtn'); // you can hide/remove it if you want
  const $cameraBtn = $('#cameraPunchBtn');

  let stream = null;
  let modelsLoaded = false;
  let referenceEntries = []; // array of {id, url, descriptor}
  let autoIntervalId = null;
  let isVerifying = false; // avoid overlapping checks

  // Load models once
  async function loadModelsIfNeeded() {
    if (modelsLoaded) return true;
    $status.text('Loading face recognition models...');
    try {
      await faceapi.nets.ssdMobilenetv1.loadFromUri(modelPath);
      await faceapi.nets.faceLandmark68Net.loadFromUri(modelPath);
      await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);
      modelsLoaded = true;
      $status.text('Models loaded.');
      return true;
    } catch (err) {
      console.error('Model load error', err);
      $status.text('Failed to load models. Check /models path.');
      return false;
    }
  }

  // Compute descriptors for all reference images
  async function computeAllReferenceDescriptors() {
    referenceEntries = [];
    const refs = window.referenceImages || [];
    if (!refs.length) {
      $status.text('No reference images available.');
      return false;
    }
    $status.text('Processing reference images...');
    const promises = refs.map(async (r) => {
      try {
        const img = await faceapi.fetchImage(r.url);
        const det = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
        if (det && det.descriptor) {
          referenceEntries.push({ id: r.id, url: r.url, descriptor: det.descriptor });
          return true;
        } else {
          console.warn('No face in reference image:', r.id);
          return false;
        }
      } catch (err) {
        console.error('Error processing reference image:', r.url, err);
        return false;
      }
    });
    await Promise.all(promises);
    if (!referenceEntries.length) {
      $status.text('No valid faces found in reference images.');
      return false;
    }
    $status.text('Loaded ' + referenceEntries.length + ' reference face(s).');
    return true;
  }

  // Start camera
  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
      video.srcObject = stream;
      await video.play();
      $status.text('Camera ready.');
      return true;
    } catch (err) {
      console.error('Camera error', err);
      $status.text('Unable to access camera. Allow camera permission.');
      return false;
    }
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
      stream = null;
    }
    try { video.pause(); } catch(e) {}
    video.srcObject = null;
  }

  // Run a single capture and verify — returns best match object or null
  async function singleVerifyAttempt() {
    // guard
    if (!video || video.readyState < 2) {
      // video not ready
      return null;
    }

    // draw frame
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // detect and descriptor
    const det = await faceapi.detectSingleFace(canvas).withFaceLandmarks().withFaceDescriptor();
    if (!det) {
      return null;
    }

    const queryDescriptor = det.descriptor;
    let best = { id: null, url: null, distance: Infinity };

    for (const ref of referenceEntries) {
      const d = faceapi.euclideanDistance(ref.descriptor, queryDescriptor);
      if (d < best.distance) {
        best = { id: ref.id, url: ref.url, distance: d };
      }
    }

    return best.distance === Infinity ? null : best;
  }

  // Start automatic loop
  function startAutoVerifyLoop() {
    if (autoIntervalId) return;
    isVerifying = false;
    autoIntervalId = setInterval(async () => {
      if (isVerifying) return; // prevent overlap
      isVerifying = true;
      try {
        const best = await singleVerifyAttempt();
        if (!best) {
          $status.text('No face detected. Keep camera steady.');
        } else {
          $status.text('Best distance: ' + best.distance.toFixed(3));
          if (best.distance <= MATCH_THRESHOLD) {
            $result.html('<div class="text-success">Face matched (d=' + best.distance.toFixed(3) + '). Punching...</div>');
            // stop loop & camera before punching
            stopAutoVerifyLoop();
            // trigger server punch (passes matchedReferenceId)
            triggerPunch(best.id);
            return;
          } else {
            $result.html('<div class="text-warning">No match (d=' + best.distance.toFixed(3) + ').</div>');
          }
        }
      } catch (err) {
        console.error('Auto verify error', err);
      } finally {
        isVerifying = false;
      }
    }, AUTO_INTERVAL_MS);
    $status.text('Auto verification started.');
  }

  function stopAutoVerifyLoop() {
    if (autoIntervalId) {
      clearInterval(autoIntervalId);
      autoIntervalId = null;
    }
    $status.text('');
  }

  // reuse your existing triggerPunch function (sends matchedReferenceId)
  function triggerPunch(matchedReferenceId = null) {
    const $btn = $cameraBtn;
    const action = $btn.data('action');
    const checkInUrl = $btn.data('checkin-url');
    const checkOutUrl = $btn.data('checkout-url');

    let url, payload;
    if (action === 'check_out') {
      url = checkOutUrl;
      payload = $('#endDayForm').length ? $('#endDayForm').serializeArray() : {};
    } else {
      url = checkInUrl;
      payload = $('#checkInForm').length ? $('#checkInForm').serializeArray() : {};
    }

    if (matchedReferenceId) {
      if (Array.isArray(payload)) {
        payload.push({name: 'matched_reference_id', value: matchedReferenceId});
      } else if (typeof payload === 'object') {
        payload.matched_reference_id = matchedReferenceId;
      }
    }

    $.ajax({
      url: url,
      method: 'POST',
      data: payload,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function(resp) {
        if (resp.status == 1) {
          toastr["success"](resp.message || (action === 'check_out' ? 'Checked out successfully' : 'Checked in successfully'), 'Success');
          $('#cameraPunchModal').modal('hide');
          setTimeout(()=> location.reload(), 800);
        } else {
          toastr["error"](resp.message || 'Punch failed', 'Error');
        }
      },
      error: function(xhr) {
        toastr["error"]('Punch request failed', 'Error');
      }
    });
  }

  // Modal events: initialize models, descriptors, camera, then start auto loop
  $modal.on('shown.bs.modal', async function() {
    $result.html('');
    const refs = window.referenceImages || [];
    if (!refs.length) {
      $status.text('No reference images found. Upload one before using camera punch.');
      $captureBtn.prop('disabled', true);
      return;
    }

    $captureBtn.prop('disabled', true); // hide manual capture if you want
    const okModels = await loadModelsIfNeeded();
    if (!okModels) {
      $captureBtn.prop('disabled', true);
      return;
    }

    const okRefs = await computeAllReferenceDescriptors();
    if (!okRefs) {
      $captureBtn.prop('disabled', true);
      return;
    }

    const camOk = await startCamera();
    if (!camOk) {
      $captureBtn.prop('disabled', true);
      return;
    }

    // small delay to let camera stabilize
    setTimeout(() => {
      startAutoVerifyLoop();
    }, 600);
  });

  // stop everything when modal hides
  $modal.on('hidden.bs.modal', function() {
    stopAutoVerifyLoop();
    stopCamera();
    $status.text('');
    $result.html('');
    $captureBtn.prop('disabled', false);
  });

  // Optional: keep manual capture button working too (disabled while auto loop runs)
  $captureBtn.on('click', async function(e) {
    e.preventDefault();
    if (!modelsLoaded || !referenceEntries.length) {
      toastr["error"]('Models or reference faces not ready', 'Error');
      return;
    }
    // stop auto loop while doing manual attempt
    stopAutoVerifyLoop();
    $status.text('Manual capture...');
    const best = await singleVerifyAttempt();
    if (best && best.distance <= MATCH_THRESHOLD) {
      $result.html('<div class="text-success">Face matched (d=' + best.distance.toFixed(3) + '). Punching...</div>');
      triggerPunch(best.id);
    } else if (best) {
      $result.html('<div class="text-danger">No match (d=' + best.distance.toFixed(3) + ').</div>');
      // restart auto loop
      startAutoVerifyLoop();
    } else {
      $result.html('<div class="text-warning">No face detected. Try again.</div>');
      startAutoVerifyLoop();
    }
  });

});
</script>
@endsection
