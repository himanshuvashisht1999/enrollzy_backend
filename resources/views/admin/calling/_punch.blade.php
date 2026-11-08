{{-- Punch in / break / punch out on the dashboards, using the CRM's HR clock (admin.hr.clock.*) and its modals.
     Needs $attendance (today's open attendance or null), $breaks (its open break or null), $punchedOut. --}}
<div class="row">
    @if(!$attendance)
        @if(!empty($punchedOut))<span class="xs muted">Punched out for today.</span>
        @else
            <button type="button" class="btn ok sm" data-bs-toggle="modal" data-bs-target="#checkInModal">🟢 Punch in</button>
            <button type="button" class="btn sm" data-bs-toggle="modal" data-bs-target="#cameraModal" onclick="ccPunchType('check_in')">📷 Camera punch in</button>
        @endif
    @else
        @if($breaks)<button type="button" class="btn sm" data-bs-toggle="modal" data-bs-target="#endLunchBreak">▶ End {{ $breaks->type }} break</button>
        @else<button type="button" class="btn sm" data-bs-toggle="modal" data-bs-target="#breakTimeModal">☕ Take a break</button>@endif
        <button type="button" class="btn bad sm" data-bs-toggle="modal" data-bs-target="#logoutDaymodal">Punch out</button>
    @endif
</div>

@once
@include('admin.hr.attendance.modals')
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4">
    <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold text-primary">📷 Verify your face</h5><button type="button" class="btn-close" data-bs-dismiss="modal" onclick="Webcam.reset()"></button></div>
    <div class="modal-body text-center p-4">
        <div id="my_camera" class="rounded-4 overflow-hidden border mx-auto mb-3" style="width:320px;height:240px;background:#000"></div>
        <button type="button" class="btn btn-primary rounded-pill fw-bold px-4" onclick="ccCapture()">Capture & confirm</button>
    </div>
</div></div></div>
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>
    var ccPunch = 'check_in';
    function ccPunchType(t) { ccPunch = t; Webcam.set({ width: 320, height: 240, image_format: 'jpeg', jpeg_quality: 90 }); Webcam.attach('#my_camera'); }
    function ccCapture() {
        Webcam.snap(function (uri) {
            $.post(ccPunch === 'check_in' ? "{{ route('admin.hr.clock.check_in') }}" : "{{ route('admin.hr.clock.check_out') }}",
                { _token: "{{ csrf_token() }}", image_data: uri, attendance_id: "{{ $attendance->id ?? '' }}", work_from: 'office', comment: 'Camera Punch' },
                function (r) { r.status == 1 ? (toastr.success(r.message), setTimeout(function () { location.reload(); }, 800)) : toastr.error(r.message); });
        });
    }
    // the HR clock's own forms (attendance modals)
    [['#checkBtn', "{{ route('admin.hr.clock.check_in') }}", '#checkInForm'], ['#breakBtn', "{{ route('admin.hr.clock.start_break') }}", '#breakForm'],
     ['#endBreakBtn', "{{ route('admin.hr.clock.end_lunchBreak') }}", '#endLunchForm'], ['#logoutBtn', "{{ route('admin.hr.clock.check_out') }}", '#endDayForm']].forEach(function (a) {
        var busy = false;
        $(document).on('click', a[0], function (e) {
            e.preventDefault(); if (busy) return; busy = true;
            $.ajax({ type: 'POST', url: a[1], data: $(a[2]).serialize(),
                success: function (r) { busy = false; r.status == 1 ? (toastr.success(r.message), setTimeout(function () { location.reload(); }, 800)) : toastr.error(r.message); },
                error: function () { busy = false; toastr.error('Something went wrong. Please try again.'); } });
        });
    });
</script>
@endpush
@endonce
