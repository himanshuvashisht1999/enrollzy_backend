@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Applied Leaves </h6>
                <div>
                    @if (Auth::guard('admin')->user()->role == 'superadmin')
                        <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#changePolicyModal"
                            href="javascript:;"> <i class="fa fa-edit"></i> Leave
                            Policies</a>
                    @endif
                    <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#viewLeavePolicyModal"
                        href="javascript:;"> <i class="fa fa-eye"></i> Leave
                        Policies</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.leaves.create') }}">Apply Leave</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Name </th>
                                <th>Subject</th>
                                <th>From to Till</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leave as $val)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $val->employee->name }}</td>
                                    <td>{{ Str::limit($val->subject, 50, '...') }}</td>
                                    <td>{{ date('d M, Y', strtotime($val->date_from)) . ' to ' . date('d M, Y', strtotime($val->date_till)) }}
                                    </td>
                                    <td>{{ date('d M, Y', strtotime($val->return_date)) }} </td>
                                    <td>{!! GetStatusBadge($val->status) !!}</td>
                                    <td>
                                        <a href="{{ route('admin.leaves.edit', encrypt($val->id)) }}" class="btn btn-sm"> <i
                                                class="fa fa-eye fa-primary"></i> </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- ------ Update leave policy start----------- --}}
            <div class="modal fade" id="changePolicyModal" tabindex="-1" role="dialog" aria-labelledby="changePolicyModal"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <textarea name="leave_policy" id="leave_policy" class="form-control">{!! $leavePolicy !!}</textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary btn-sm" href="javascript:;" id="updatePolicy">Update</a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ------ Update leave policy end----------- --}}
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
    <script src="{{ URL::asset('admin/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const useDarkMode = false; // Always set to false to force light mode
            const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
            tinymce.init({
                selector: 'textarea#leave_policy',
                license_key: 'gpl', // Indicate agreement to the GPL license
                plugins: 'preview searchreplace autolink autosave save code fullscreen wordcount help charmap emoticons advlist directionality emoticons image importcss insertdatetime link lists media nonbreaking pagebreak preview save searchreplace table wordcount',
                menubar: 'file edit view format tools help',
                toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
                autosave_ask_before_unload: false,
                autosave_interval: '30s',
                autosave_retention: '2m',
                height: 600,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
                noneditable_class: 'mceNonEditable',
                toolbar_mode: 'sliding',
                contextmenu: 'link',
                promotion: false,
                skin: useDarkMode ? 'oxide-dark' : 'oxide',
            });
            // ------------------------------------ jQuery end here
            $('#updatePolicy').click(function(e) {
                e.preventDefault();
                var leavePolicy = tinymce.get('leave_policy').getContent();
                if (leavePolicy.trim() === "") {
                    toastr["warning"]('Leave policy cannot be empty', 'Warning!');
                    return;
                }
                $.ajax({
                    url: "{{ route('admin.setting.store') }}",
                    type: 'POST',
                    data: {
                        leave_policy: leavePolicy,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 1) {
                            toastr["success"](response.message, 'Success!');
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr["error"](response.message, 'Error!');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr["warning"](response.message, 'Warning!');
                    }
                });
            });
        });
    </script>
@endsection
