@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Advance / Bonus Staff Payment </h6>
                @can('advancepay-add')
                <a class="btn btn-primary btn-sm" href="{{ route('admin.advance.create') }}">Pay Advance / Bonus</a>
                @endcan
            </div>
            @can('advancepay-browse')
            <div class="card-body">
                <form class="row" >
                    @csrf
                    <div class="col-md-3 form-group">
                        <label for="employeeId">Select Staff</label>
                        <select name="staff_id" id="employeeId" class="form-control" required>
                        <option value="">Select Staff</option>
                            @foreach ($employees as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }} - {{ $item->designation->name ?? 'Not Assigned' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="yearId">Select From Date</label>
                        <input type="date" name="from" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="yearId">Select To Date</label>
                        <input type="date" name="to" class="form-control">
                    </div>
                    <div class="col-lg-3 form-group text-center " style="align-content: center">
                        <button type="submit" class="btn btn-success">Search</button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
            @endcan
        </div>
        @if($advance != null)
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Advance / Bonus Staff Payment </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                @can('advancepay-browse')
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Staff </th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Type</th>
                                <th>Balance</th>
                                <th>Clearance At</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($advance as $val)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $val->employee->name }} </td>
                                    <td style="color:red;">{{ $val->debit }} </td>
                                    <td style="color:green;">{{ $val->credit }} <br>
                                    @php
                                        $payoutData = DB::table('employee_payout')->where('payslip_id', $val->payslip_id )->first();
                                    @endphp 
                                        @if($payoutData)
                                        <a href="{{route('admin.payOut.show', encrypt($payoutData->id))}}" target="_blank">{{ $payoutData->payslip_id }}</a> 
                                        @endif   
                                    </td>
                                    <td>{{ $val->transaction_for }} </td>
                                    <td style="color:blue;">{{ $val->balance}}</td>
                                    <td>{{ date('h:i A - d M, Y ', strtotime($val->clearance_date)) }}</td>
                                    <td>Action</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endcan
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
@section('push_script')
@endsection
