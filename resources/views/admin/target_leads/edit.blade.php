@extends('admin.layouts.master')

@section('title', 'Edit Target Lead')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.target-leads.index') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
    <h3 class="fw-bold mt-2">Edit Target</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.target-leads.update', $targetLead->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Select Staff Member</label>
                    <select name="staff_id" class="form-select select2" required>
                        <option value="">-- Select --</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ old('staff_id', $targetLead->staff_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Year</label>
                    <select name="year" class="form-select select2" required>
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                            <option value="{{ $y }}" {{ old('year', $targetLead->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Month</label>
                    <select name="month" class="form-select select2" required>
                        @php $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']; @endphp
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ old('month', $targetLead->month) == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Calling Target</label>
                    <input type="number" name="month_target_calling" class="form-control" value="{{ old('month_target_calling', $targetLead->month_target_calling) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Admissions Target</label>
                    <input type="number" name="month_target_admissions" class="form-control" value="{{ old('month_target_admissions', $targetLead->month_target_admissions) }}" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Update Target</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
