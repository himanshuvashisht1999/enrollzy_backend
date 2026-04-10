@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
<div class="container-fluid">
    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Applied for leaves </h6>
            <a class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#viewLeavePolicyModal"
                href="javascript:;"> <i class="fa fa-eye"></i> Leave Policies</a>
        </div>
        <div class="card-body">
            <form class="row" id="applyLeaveForm" method="POST"
                action="{{ route('admin.leaves.update', encrypt($leave->id)) }}">
                @csrf
                @method('PATCH')
                <div class="col-md-8 form-group">
                    <label for="subject">Title / Subject {!! GetStatusBadge($leave->status) !!}</label>
                    <input type="text" class="form-control" readonly name="subject" value="{{ $leave->subject }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_from">Apply Date</label>
                    <input type="text" class="form-control" readonly
                        value="{{ date('d M, Y h:i A', strtotime($leave->apply_date)) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_from">Leave From</label>
                    <input type="text" class="form-control" readonly
                        value="{{ date('d M, Y', strtotime($leave->date_from)) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_till">Leave Till</label>
                    <input type="text" class="form-control" readonly
                        value="{{ date('d M, Y', strtotime($leave->date_till)) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="return_date">Return date</label>
                    <input type="text" class="form-control" readonly
                        value="{{ date('d M, Y', strtotime($leave->return_date)) }}">
                </div>
                <div class="col-md-12 form-group">
                    <label for="content">Your Message for leave</label>
                    <textarea name="content" class="form-control" disabled>{{ $leave->content }}</textarea>
                </div>
                @if (Auth::guard('admin')->user()->role == 'superadmin' || Auth::guard('admin')->user()->role ==
                'admin')
                <div class="col-md-6 form-group">
                    <label for="status">Status</label>
                    <select name="status" id="statusChange" class="form-control">
                        <option {{ $leave->status == 'pending' ? 'selected' : '' }} value="pending"> Pending
                        </option>
                        <option {{ $leave->status == 'approved' ? 'selected' : '' }} value="approved"> Approve
                        </option>
                        <option {{ $leave->status == 'rejected' ? 'selected' : '' }} value="rejected"> Reject
                        </option>
                        <option {{ $leave->status == 'unapprove' ? 'selected' : '' }} value="unapprove">
                            Un Approve Leave Taken</option>
                    </select>
                </div>
                <div class="col-md-6 form-group " id="fineField">
                    <label for="fine">Fine / Panelty</label>
                    <input type="text" class="form-control" name="fine" value="{{ $penalty }}">
                </div>
                <div class="col-md-12 form-group">
                    <label for="log">Admin Comment</label>
                    <textarea name="log" class="form-control">{{ old('log') }}</textarea>
                </div>
                @endif
            </form>
        </div>
        @if (Auth::guard('admin')->user()->role == 'superadmin' || Auth::guard('admin')->user()->role == 'admin')
        <div class="card-footer text-right">
            <a href="{{ route('admin.leaves.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
            <button type="submit" form="applyLeaveForm" class="btn btn-primary btn-sm">Update Status</a>
        </div>
        @endif
        <div class="card-body mt-4">
            <h5> Attachments</h5>
            @php
            $leaveFile = explode(',', $leave->files);
            @endphp
            <div class="row">
                @foreach ($leaveFile as $file)

                @php
                // Get the file extension
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                // Prepare the file URL

                $fileUrl = URL::asset($file);
                @endphp
                <div class="col-lg-3">
                    <a href="{{ URL::asset('/') }}{{ $file }}" target="_blank">
                        <img src="{{ URL::asset('/') }}{{ $file }}" alt="" width="200px">


                        @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                        <!-- Image File -->
                        <img src="{{ $fileUrl }}" alt="Image">
                        @elseif ($extension == 'pdf')
                        <!-- PDF File -->
                        <iframe src="{{ $fileUrl }}" frameborder="0"></iframe>
                        <a href="{{ $fileUrl }}" target="_blank" >View Document </a>
                        @elseif (in_array($extension, ['doc', 'docx']))
                        <!-- Document File -->
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary">View Document <i
                                class="fa fa-eye"></i></a>
                        @else
                        <!-- Default case or unsupported file type -->
                        <p>Unsupported file type</p>
                        @endif
                    </a>
                </div>
                @endforeach
            </div>
            <hr>
        </div>
        <div class="card-body mt-4">
            <h5> logs</h5>
            @foreach ($logData as $log)
            <ul>
                <li>Status : <b> {!! GetStatusBadge($log['status']) !!}</b></li>
                <li>Message : <b>{{ $log['admin_message'] }}</b></li>
                <li>Fine : <b>{{ $log['fine'] }}</b></li>
                <li>Time : <b>{{ $log['timestamp'] }}</b></li>
            </ul>
            <hr>
            @endforeach
        </div>
        {{-- ------ view leave policy start----------- --}}
        <div class="modal fade" id="viewLeavePolicyModal" tabindex="-1" role="dialog"
            aria-labelledby="viewLeavePolicyModal" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Leave Policies</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {!! $leavePolicy !!}
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ------ view leave policy end----------- --}}
    </div>
</div>
@endsection
@section('push_script')
<script>
    $('#statusChange').on('change', function () {
        $('#fineField').toggleClass('d-none', $(this).val() !== 'unapprove');
    });

</script>
@endsection
