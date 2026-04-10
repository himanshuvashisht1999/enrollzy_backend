@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Apply for leaves </h6>
                <a class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#viewLeavePolicyModal"
                    href="javascript:;"> <i class="fa fa-eye"></i> Leave Policies</a>
            </div>
            <div class="card-body">
                <form class="row" id="applyLeaveForm" method="POST" action="{{ route('admin.leaves.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-6 form-group">
                        <label for="leave_type">Leave Type</label>
                        <select name="leave_type_id" id="leave_type" class="form-control">
                            <option></option>
                            @foreach ($leaveSetting as $lStng)
                                <option value="{{ $lStng->id }}"> {{ $lStng->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="subject">Title / Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="date_from">Leave From</label>
                        <input type="date" class="form-control" name="date_from"
                            value="`">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="date_till">Leave Till</label>
                        <input type="date" class="form-control" name="date_till" id="date_till"
                            value="">
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="return_date">Return date</label>
                        <input type="date" class="form-control" name="return_date" id="return_date"
                            value="" readonly>
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="content">Reason for absence</label>
                        <textarea name="content" class="form-control">{{ old('content') }}</textarea>
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="files">Files / Attachments</label>
                        <input type="file" multiple name="files[]" class="form-control">
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('admin.leaves.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="applyLeaveForm" class="btn btn-primary btn-sm">Apply now</a>
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
                            <ul>
                                @foreach($policies as $Policy)
                                <li>
                                    {!! $Policy->policy !!}
                                </li>
                                @endforeach
                            </ul>
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
    document.getElementById('date_till').addEventListener('change', function() {
        var dateTill = new Date(this.value);
        
        // Increment the date by 1 day
        dateTill.setDate(dateTill.getDate() + 1);

        // Format the new date as yyyy-mm-dd
        var returnDate = dateTill.toISOString().split('T')[0];

        // Set the return_date input value to the new date and disable the field
        document.getElementById('return_date').value = returnDate;
        document.getElementById('return_date').disabled = false;
    });
</script>
@endsection
