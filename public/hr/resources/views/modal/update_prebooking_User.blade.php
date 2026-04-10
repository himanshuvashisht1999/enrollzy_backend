<div class="modal fade" id="updateMobileModel" tabindex="-1" role="dialog" aria-labelledby="updateMobileModel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" id="MobileModelSection">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="mobileEntryForm" name="mobileEntryForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> Mobile Number</label>
                            <input type="text" class="form-control" name="phone" placeholder="mobile number"
                                value="{{ $masterOrder->user->phone }}">
                            <input type="hidden" name="user_id" value="{{ $masterOrder->user->id }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" id="sendMobileFormBtn" href="javascript:;"> Send OTP </a>
            </div>
        </div>
        <div class="modal-content d-none" id="MobileOTPSection">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Or Verify Mobile Number</h5>
            </div>
            <div class="modal-body">
                <form id="mobileOTPForm" name="mobileOTPForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="name"> OTP</label>
                            <input type="text" class="form-control" name="otp">
                            <input type="hidden" name="user_id" value="{{ $masterOrder->user->id }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" id="ResendBtnMobile" href="javascript:;"> Resend OTP </a>
                <a class="btn btn-primary" id="verifyMobileOtpBtn" href="javascript:;"> Verify </a>
            </div>
        </div>
    </div>
</div>
