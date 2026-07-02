@extends('admin.layouts.master')

@section('title', 'Mentor General Commission & Priority')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Mentor General Commission & Priority</h4>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.mentor.commissions.store') }}" method="POST">
                        @csrf
                        <h5 class="mb-3">Global Commission</h5>
                        <div class="mb-4">
                            <label for="commission_percentage" class="form-label">Platform Commission Percentage</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="commission_percentage" name="commission_percentage" 
                                       value="{{ old('commission_percentage', $commission->commission_percentage ?? 15) }}" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted mt-1 d-block">This percentage will be used as a fallback if no higher priority commission is set.</small>
                        </div>

                        <hr>
                        <h5 class="mb-3 mt-4">Commission Priority Order</h5>
                        <p class="text-muted small">Select the order in which the platform should check for a commission rate (Rank 1 is checked first).</p>
                        
                        @php
                            $priority = $commission->priority_order ?? ['degree', 'industry', 'level', 'global'];
                        @endphp

                        <div class="row">
                            @for($i = 0; $i < 4; $i++)
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Rank {{ $i + 1 }}</label>
                                <select class="form-select" name="priority_{{ $i + 1 }}" required>
                                    <option value="degree" {{ (old('priority_'.($i+1), $priority[$i] ?? '')) == 'degree' ? 'selected' : '' }}>Degree</option>
                                    <option value="industry" {{ (old('priority_'.($i+1), $priority[$i] ?? '')) == 'industry' ? 'selected' : '' }}>Industry</option>
                                    <option value="level" {{ (old('priority_'.($i+1), $priority[$i] ?? '')) == 'level' ? 'selected' : '' }}>Mentee Level</option>
                                    <option value="global" {{ (old('priority_'.($i+1), $priority[$i] ?? '')) == 'global' ? 'selected' : '' }}>Global Default</option>
                                </select>
                            </div>
                            @endfor
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Save Commission Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
