@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Update Marquee Slide </h6>
            </div>
            <div class="card-body">
                <form id="updateMarqueeForm" action="{{ route('admin.marque.update', encrypt($marque->id)) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Slide Content (Use 20 to 25 words for betterr Design..)</label>
                            <input type="text" class="form-control" name="content" value="{{ $marque->content }}"
                                placeholder="content">
                        </div>

                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.marque.index') }}">Cancel </a>
                <button type="submit" form="updateMarqueeForm" class="btn btn-primary btn-sm">Update</button>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
@endsection
