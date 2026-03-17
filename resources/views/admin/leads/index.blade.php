@extends('admin.layouts.master')

@section('title', 'Manage Leads')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">
        @if(request('type') == 'Expert') Expert Leads
        @elseif(request('type') == 'Alumni') Alumni Leads
        @else Student Leads @endif
    </h4>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('leads.index') }}" method="GET" class="row g-3">
            <input type="hidden" name="type" value="{{ request('type') }}">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
            @if(request()->anyFilled(['search', 'status']))
                <div class="col-md-2">
                    <a href="{{ route('leads.index', ['type' => request('type')]) }}" class="btn btn-outline-danger w-100">Clear</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">@if(request('type') == 'Expert') Expert @elseif(request('type') == 'Alumni') Alumnus @else Student @endif</th>
                        <th>Contact info</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Received At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $lead->name }}</div>
                            <span class="badge bg-light text-muted small fw-normal border">{{ $lead->subject ?? 'General Inquiry' }}</span>
                        </td>
                        <td>
                            <div class="small"><i class="fas fa-envelope me-1 text-muted"></i> {{ $lead->email }}</div>
                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i> {{ $lead->phone }}</div>
                        </td>
                        <td>
                            <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $lead->message }}">
                                {{ $lead->message }}
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('leads.status', $lead->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm {{ 
                                    $lead->status == 'New' ? 'text-primary' : (
                                    $lead->status == 'Contacted' ? 'text-info' : (
                                    $lead->status == 'Converted' ? 'text-success' : 'text-danger')) 
                                }} border-0 bg-light fw-bold shadow-none" style="width: auto;">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ $lead->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td><div class="text-muted small">{{ $lead->created_at->format('d M, Y H:i') }}</div></td>
                        <td class="text-end pe-4">
                            <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0" onclick="return confirm('Delete this lead records?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No leads found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white pt-3 border-0">
        {{ $leads->appends(request()->query())->links() }}
    </div>
</div>
@endsection
