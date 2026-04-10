@extends('layouts.app')
@section('push_css')
    <link href="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')

@php
    $user_id = Auth::guard('admin')->user()->organization_id;
    $qrUrl = 'https://hrzenix.com/admin/whatsapp-qr/qr_org_'.$user_id.'.png';
    $hasQr = false;

    $headers = @get_headers($qrUrl, 1);
    if ($headers && isset($headers[0]) && str_contains($headers[0], '200') && isset($headers['Content-Type']) && str_contains($headers['Content-Type'], 'image')) {
        $hasQr = true;
    }
@endphp

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Whatsapp Report</h6>
                <a class="btn btn-primary btn-sm stop-whatsapp-btn" href="{{ route('admin.whatsapp_template.whatsappStop') }}">
                    <i class="fas fa-stop fa-sm text-white-50"></i> Stop Whatsapp Message
                </a>
                @if ($hasQr)
                <a href="{{$qrUrl}}" target="_blank" 
                    style="
                        display: inline-block;
                        padding: 6px 22px;
                        background: linear-gradient(135deg, #25D366, #128C7E);
                        color: #fff;
                        font-size: 16px;
                        font-weight: 600;
                        text-decoration: none;
                        border-radius: 8px;
                        box-shadow: 0 3px 10px rgba(18, 140, 126, 0.3);
                        transition: all 0.3s ease;
                    "
                    onmouseover="this.style.background='linear-gradient(135deg,#128C7E,#075E54)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #25D366, #128C7E)'"
                    >
                    📱 View WhatsApp QR Code
                </a>
                @endif
            </div> 
            <div class="container-fluid row mt-2">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="filter" class="btn btn-primary mr-2">Filter</button>
                    <button id="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Number</th>
                                    <th>Time Gap</th>
                                    <th>Message</th>
                                    <th>Caption</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->number }} </td>
                                        <td>{{ $item->time_gap_from_previous_message }} </td>
                                        <td>{!! $item->message !!} </td>
                                        <td>{{ $item->caption }} </td>
                                        
                                        <td class="text-capitalize">
                                            {{$item->status }}
                                        </td>
                                        <td>{{ date('h:i A - d M, Y ', strtotime($item->created_at)) }}</td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('push_script')
    <script src="{{ URL::asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
            });

            // --- Custom date filter ---
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var min = $('#from_date').val();
                var max = $('#to_date').val();
                var createdAt = data[6]; // 'Created At' column index

                // If no filter, show all
                if (!min && !max) return true;

                // Parse created_at string (format: 05:15 PM - 13 Jun, 2025)
                var match = createdAt.match(/(\d{2}):(\d{2}) ([AP]M) - (\d{2}) (\w+), (\d{4})/);
                if (!match) return true;

                const months = {
                    Jan: 0, Feb: 1, Mar: 2, Apr: 3, May: 4, Jun: 5,
                    Jul: 6, Aug: 7, Sep: 8, Oct: 9, Nov: 10, Dec: 11
                };

                let hour = parseInt(match[1]);
                const minute = parseInt(match[2]);
                const ampm = match[3];
                const day = parseInt(match[4]);
                const month = months[match[5]];
                const year = parseInt(match[6]);

                if (ampm === "PM" && hour !== 12) hour += 12;
                if (ampm === "AM" && hour === 12) hour = 0;

                var rowDate = new Date(year, month, day, hour, minute);

                // Normalize filter range
                var fromDate = min ? new Date(min + ' 00:00:00') : null;
                var toDate = max ? new Date(max + ' 23:59:59') : null;

                // Include if within range (including equal)
                if ((!fromDate || rowDate >= fromDate) && (!toDate || rowDate <= toDate)) {
                    return true;
                }
                return false;
            });

            // --- Buttons ---
            $('#filter').click(function() {
                table.draw();
            });

            $('#reset').click(function() {
                $('#from_date').val('');
                $('#to_date').val('');
                table.draw();
            });
        });

        // ------------ Stop Whatsapp Message Confirmation -------------
        $('.stop-whatsapp-btn').click(function(event) {
            event.preventDefault();
            let href = $(this).attr('href');

            swal({
                title: "Are you sure?",
                text: "This will stop all running WhatsApp messages.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willStop) => {
                if (willStop) {
                    window.location.href = href;
                } else {
                    swal("Cancelled", "WhatsApp messages are still running 🤗", "error");
                }
            });
        });
       
    </script>
@endsection
