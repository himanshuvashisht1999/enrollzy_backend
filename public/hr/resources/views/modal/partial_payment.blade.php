<div class="modal fade" id="partialModal" tabindex="-1" role="dialog" aria-labelledby="partialModal" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 720px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Make Partial Payment</h4>
                <h4>
                    Balance Amount -
                    <span class="badge badge-danger">
                        @if (count($receivedPayment) > 0)
                            {{ env('CURRENCY') . ' ' . number_format($grandTotal - ($receivedAmount ?? 0), 2) }}
                        @else
                            {{ env('CURRENCY') . ' ' . number_format($masterOrder->payment_status == 'paid' ? 0 : $grandTotal, 2) }}
                        @endif
                    </span>
                </h4>
            </div>
            <div class="modal-body">
                <form id="partialSubmitForm" name="partialSubmitForm">
                    @csrf
                    <div class="row">
                        @php
                            $userWallet = $masterOrder->user->wallet;
                        @endphp
                        <div class="col-md-6 form-group">
                            <label for="status"> Mode of Payment </label>
                            <select name="payment_mode" class="form-control">
                                <option value="" selected disabled></option>
                                <option value="wallet">User Wallet -
                                    {{ $userWallet->balance ?? ' Wallet not found' }}
                                </option>
                                <option value="current_account">Cheque Current Account</option>
                                <option value="cc_account">Cheque CC Account</option>
                                <option value="paytm">Paytm </option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="name">Transaction Number</label>
                            <input type="text" name="txn_number" class="form-control">
                            <input type="hidden" name="order_id" value="{{ $masterOrder->id }}">
                            <input type="hidden" name="order_no" value="{{ $masterOrder->order_no }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="name">receivable Amount</label>
                            <input type="text" class="form-control" name="receivable" readonly
                                value="@if (count($receivedPayment) > 0) {{ number_format($grandTotal - ($receivedAmount ?? 0), 2) }}@else{{ intval($masterOrder->payment_status == 'paid' ? 0 : $grandTotal) }} @endif">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="name">Recieve Amount</label>
                            <input type="text" class="form-control" name="receive_amount" id="payingAmount">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="name">Rest Amount</label>
                            <input type="text" class="form-control" name="rest_amount" id="restPayable" readonly>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="status"> Comment</label>
                            <textarea name="comment" class="form-control"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" id="makePartialPay" href="javascript:;"> Add Payment </a>
            </div>
        </div>
    </div>
</div>
