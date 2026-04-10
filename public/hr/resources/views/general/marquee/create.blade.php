@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Create Marquee Slide </h6>
            </div>
            <div class="card-body">
                <form id="addMarqueeForm" action="{{ route('admin.marque.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Slide Content (Use 20 to 25 words for betterr Design..)</label>
                            <input type="text" class="form-control" name="content" value="{{ old('content') }}"
                                placeholder="content">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.marque.index') }}">Cancel </a>
                <button type="submit" form="addMarqueeForm" class="btn btn-primary btn-sm">Add</button>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
@endsection
