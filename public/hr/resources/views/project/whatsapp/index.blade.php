@extends('layouts.app')
@section('push_css')
<link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
@endsection
@section('content')
<div class="container-fluid">
   
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Send Whatsapp Message</h6>

        </div>
        <div class="card-body">
            <form id="sort_product" class="row" action="{{ route('admin.whatsapp.send') }}" name="sort_product" method="post">
                @csrf
                <input type="hidden" name="group" value="1">
                <div class="form-group col-lg-3">
                    <input name="mobile" class="form-control m-2" placeholder="enter mobile number">
                </div>
                <div class="form-group col-lg-3">
                <input name="message" class="form-control m-2" placeholder="enter message">
                </div>
                <div class="col-lg-3">
                    <button class="btn btn-primary" type="submit" id="ubmitSearchButton" disabled>Send Message</button>
                    <a href="{{ route('admin.calling.index') }}" class="btn btn-info">Reset</a><p> api keys implementaion in env are in progress</p>
                </div>
            </form>
        </div>
    </div>


</div>

@endsection
@push('push_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>



</script>
@endpush
