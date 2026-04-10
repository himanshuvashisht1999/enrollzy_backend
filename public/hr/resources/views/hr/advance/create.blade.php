@extends('layouts.app')
@section('push_css')
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Content Row -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Make Advance / Bonus Staff Payment </h6>
            </div>
            @can('advancepay-add')
            <div class="card-body">
                <form class="row" id="paymentModalForm" method="POST" action="{{ route('admin.advance.store') }}">
                    @csrf
                    <div class="form-group col-sm-3">
                        <label for="staff_id">Select Staff </label>
                        <select id="staff_id" class="form-control" name="staff_id">
                        <option value="" disabled {{ old('staff_id') ? '' : 'selected' }}>Select Staff</option>
                            @foreach ($staff as $emp)
                                <option {{ old('staff_id') == $emp->id ? 'selected' : '' }} value='{{ $emp->id }}'>
                                    {{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="paying_for">Paying for </label>
                        <select id="paying_for" class="form-control" name="paying_for">
                            <option value=''></option>
                            <option {{ old('paying_for') == 'advance' ? 'selected' : '' }} value='advance'>Advance</option>
                            <option {{ old('paying_for') == 'penalty' ? 'selected' : '' }} value='penalty'>Penalty</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-3">
                        <label for="txn_type">Txn type </label>
                        <select id="txn_type" class="form-control" name="txn_type">
                            <option value=''></option>
                            <option {{ old('txn_type') == 'debit' ? 'selected' : '' }} value='debit'>Debit ( You are Paying
                                it To Staff)</option>
                            <option {{ old('txn_type') == 'credit' ? 'selected' : '' }} value='credit'>Credit ( You are
                                receiving / deducting from Staff)</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="debit_account">Bank Account </label>
                        <select id="debit_account" class="form-control" name="debit_account">
                        <option value="" disabled {{ old('staff_id') ? '' : 'selected' }}>Select Bank Account</option>
                            <option {{ old('debit_account') == 'cash' ? 'selected' : '' }} value='cash'>Cash</option>
                            <option {{ old('debit_account') == 'current_sbi' ? 'selected' : '' }} value='current_sbi'>
                                Current SBI</option>
                            <option {{ old('debit_account') == 'cce_sbi' ? 'selected' : '' }} value='cce_sbi'>CCE SBI
                            </option>
                            <option {{ old('debit_account') == 'current_bob' ? 'selected' : '' }} value='current_bob'>
                                Current BOB</option>
                            <option {{ old('debit_account') == 'cce_bob' ? 'selected' : '' }} value='cce_bob'>CCE BOB
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="payment_method">Payment Method </label>
                        <select id="payment_method" class="form-control" name="payment_method">
                        <option value="" disabled {{ old('staff_id') ? '' : 'selected' }}>Payment Method</option>
                            <option {{ old('payment_method') == 'cash' ? 'selected' : '' }} value='cash'>Cash</option>
                            <option {{ old('payment_method') == 'gpay' ? 'selected' : '' }} value='gpay'>G Pay </option>
                            <option {{ old('payment_method') == 'paytm' ? 'selected' : '' }} value='paytm'>Paytm </option>
                            <option {{ old('payment_method') == 'phone_pay' ? 'selected' : '' }} value='phone_pay'>Phone
                                Pay </option>
                            <option {{ old('payment_method') == 'net_banking' ? 'selected' : '' }} value='net_banking'>Net
                                Banking </option>
                            <option {{ old('payment_method') == 'cheque' ? 'selected' : '' }} value='cheque'>Cheque
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="amount">Paying Amount</label>
                        <input type="number" required class="form-control" id="amount" name="amount"
                            value="{{ old('amount') }}" />
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="bank_charges">Bank Charges</label>
                        <input type="text" required class="form-control" id="bank_charges" name="bank_charges"
                            value="{{ old('bank_charges') ?? '0' }}" />
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="txn_id">Cheque / Txn No:</label>
                        <input type="text" required class="form-control" id="txn_id" name="txn_id"
                            value="{{ old('txn_id') }}" />
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="clearance_date">Clearance Date:</label>
                        <input type="date" required class="form-control" id="clearance_date" name="clearance_date"
                            value="" />
                    </div>
                    <div class="form-group col-sm-2">
                        <label for="initiation_date">Initiation Date:</label>
                        <input type="date" required class="form-control" id="initiation_date" name="initiation_date"
                            value="" />
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="comment">Notes / Comments:</label>
                        <textarea class="form-control" id="comment" name="comment" maxlength="400">{{ old('comment') }}</textarea>
                    </div>
                </form>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('admin.advance.index') }}" class="btn btn-secondary btn-sm">Cancel </a>
                <button type="submit" form="paymentModalForm" class="btn btn-primary btn-sm">Add Pay</a>
            </div>
            @endcan
        </div>
    </div>
@endsection
@section('push_script')
    <script>
        $('#paying_for').on('change', function() {
            var payingFor = $(this).val();
            var txnType = $('#txn_type');
            // Reset the txn_type options to default (empty value)
            txnType.prop('disabled', false); // Enable the dropdown
            txnType.val(''); // Reset value to empty
            // Based on the selected value in 'paying_for', update 'txn_type'
            if (payingFor == 'advance' || payingFor == 'other') {
                // For advance and other, allow both debit and credit
                txnType.prop('disabled', false); // Enable the dropdown (both debit and credit options available)
            } else if (payingFor == 'bonus') {
                // For bonus, only debit is allowed
                txnType.val('debit'); // Set txn_type to 'debit'
            } else if (payingFor == 'penalty' || payingFor == 'tax') {
                // For penalty and tax, only credit is allowed
                txnType.val('credit'); // Set txn_type to 'credit'
            }
        });
        // Trigger the change event on page load to set the correct txn_type based on the initial value
        $('#paying_for').trigger('change');
    </script>
@endsection
