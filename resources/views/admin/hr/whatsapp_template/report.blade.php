@extends('admin.layouts.master')

@section('title', 'WhatsApp Delivery Report')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Message Queue & History</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.hr.whatsapp_template.whatsappStop') }}" class="btn btn-danger btn-sm rounded-pill px-3 stop-btn">
                    <i class="fas fa-stop me-1"></i> Stop All Queue
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4 bg-light p-3 rounded-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" id="from_date" class="form-control rounded-3 border-0">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" id="to_date" class="form-control rounded-3 border-0">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button id="filter" class="btn btn-primary rounded-pill px-4 flex-grow-1">Filter Report</button>
                    <button id="reset" class="btn btn-light rounded-pill px-4 border">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="reportTable" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Recipient</th>
                            <th>Delay (s)</th>
                            <th>Message Preview</th>
                            <th>Status</th>
                            <th>Scheduled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="font-monospace text-primary fw-bold">{{ $item->number }}</span></td>
                            <td>{{ $item->time_gap_from_previous_message }}s</td>
                            <td><span class="text-truncate d-inline-block" style="max-width: 200px;">{{ $item->message }}</span></td>
                            <td>
                                @php
                                    $statusClass = [
                                        'sent' => 'bg-success',
                                        'draft' => 'bg-warning text-dark',
                                        'cancelled' => 'bg-danger',
                                        'processing' => 'bg-info'
                                    ][$item->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }} rounded-pill px-3 text-capitalize">{{ $item->status }}</span>
                            </td>
                            <td>{{ date('d M, Y', strtotime($item->created_at)) }} <br> <small class="text-muted">{{ date('h:i A', strtotime($item->created_at)) }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#reportTable').DataTable({
            order: [[5, 'desc']],
            language: { search: "_INPUT_", searchPlaceholder: "Search report..." }
        });

        // Filter Logic
        $('#filter').click(function() {
            table.draw();
        });

        $('#reset').click(function() {
            $('#from_date, #to_date').val('');
            table.draw();
        });

        $('.stop-btn').click(function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            Swal.fire({
                title: 'Emergency Stop?',
                text: "All pending messages in the queue will be cancelled.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, stop everything!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = url;
            });
        });
    });
</script>
@endpush
