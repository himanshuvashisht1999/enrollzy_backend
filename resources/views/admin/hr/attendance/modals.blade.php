<!-- check in modal -->
 <div class="modal fade" id="checkInModal" tabindex="-1" role="dialog" aria-labelledby="checkInModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="exampleModalLabel">Punch IN </h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="date-time mb-4">
                    <h5 class="fw-bold"><b> <i class="clock-icon fas fa-clock me-2 text-primary"></i> {{ date('d M - h:i A') }}</b></h5>
                </div>
                <form name="checkInForm" id="checkInForm" class="text-start">
                    @csrf
                    <div class="mb-3">
                        <label for="workingFrom" class="form-label fw-bold small uppercase">Working From <span class="text-danger">*</span></label>
                        <select class="form-select border-0 bg-light rounded-3" id="work_from" name="work_from">
                            <option value="office">Office</option>
                            <option value="home">Home</option>
                            <option value="field">Field</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label fw-bold small uppercase">Comment / Late Reason <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control border-0 bg-light rounded-3" rows="3" placeholder="Enter note..."></textarea>
                    </div>
                </form>
                <div class="d-grid mt-4">
                    <button type="button" class="btn btn-primary btn-lg rounded-pill fw-bold" id="checkBtn">Confirm Punch IN</button>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($attendance)
    <!-- Start break modal -->
    <div class="modal fade" id="breakTimeModal" tabindex="-1" role="dialog" aria-labelledby="breakTimeModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-warning" id="exampleModalLabel">Break Time</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="date-time mb-4">
                        <h5 class="fw-bold"><b> <i class="clock-icon fas fa-clock me-2 text-warning"></i> {{ date('d M - h:i A') }}</b></h5>
                    </div>
                    <form name="breakForm" id="breakForm" class="text-start">
                        @csrf
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        <div class="mb-3">
                            <label for="workingFrom" class="form-label fw-bold small uppercase">Break for <span class="text-danger">*</span></label>
                            <select class="form-control form-select border-0 bg-light rounded-3" id="break_for" name="break_for">
                                <option value="lunch">Lunch Break</option>
                                <option value="personal">Personal work</option>
                            </select>
                        </div>
                    </form>
                    <div class="d-grid mt-4">
                        <button type="button" class="btn btn-warning btn-lg rounded-pill fw-bold text-white" id="breakBtn">Start Break</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End break modal -->
    <div class="modal fade" id="endLunchBreak" tabindex="-1" role="dialog" aria-labelledby="endLunchBreak" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-success" id="exampleModalLabel">End {{ $breaks->type ?? '' }} break </h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    @if ($breaks)
                        <div class="date-time">
                            @php
                                $startTime = \Carbon\Carbon::parse($breaks->start);
                                $now = \Carbon\Carbon::now();
                                $duration = $now->diff($startTime);
                                $durationInMinutes = $duration->h * 60 + $duration->i;
                            @endphp
                            <div class="alert bg-light border-0 rounded-4 mb-4">
                                <div class="mb-2 small text-muted uppercase fw-bold ls-1">Break Duration</div>
                                <h3 class="fw-bold text-dark mb-0">
                                    {{ $duration->h }}h {{ $duration->i }}m {{ $duration->s }}s
                                </h3>
                            </div>
                            
                            @if ($breaks->type == 'lunch')
                                <div class="mb-4">
                                    @if ($durationInMinutes <= 30)
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> Under 30 mins! Good.</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i> Over limit by {{ $durationInMinutes - 30 }}m</span>
                                    @endif
                                </div>
                            @endif

                            <form name="endLunchForm" id="endLunchForm" class="text-start">
                                @csrf
                                <input type="hidden" name="break_id" value="{{ $breaks->id }}">
                                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                                @if ($breaks->type == 'lunch')
                                    @if ($durationInMinutes <= 30)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small uppercase">How was your lunch? <span class="text-danger">*</span></label>
                                            <select name="lunch_was" id="lunch" class="form-select border-0 bg-light rounded-3" required>
                                                <option value="good">Good</option>
                                                <option value="average">Average</option>
                                                <option value="poor">Poor</option>
                                            </select>
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small uppercase">Why did you take so much time? <span class="text-danger">*</span></label>
                                            <textarea name="reason" id="reason" class="form-control border-0 bg-light rounded-3" rows="3" required></textarea>
                                        </div>
                                    @endif
                                @else
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small uppercase">Why did you take personal break? <span class="text-danger">*</span></label>
                                        <textarea name="reason" id="reason" class="form-control border-0 bg-light rounded-3" rows="3" required></textarea>
                                    </div>
                                @endif
                            </form>
                        </div>
                    @endif
                    <div class="d-grid mt-4">
                        <button type="button" class="btn btn-success btn-lg rounded-pill fw-bold" id="endBreakBtn">End Break & Return</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- logout Day modal -->
    <div class="modal fade" id="logoutDaymodal" tabindex="-1" role="dialog" aria-labelledby="logoutDaymodal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="exampleModalLabel">Day End Summary</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="date-time mb-4 text-muted small uppercase fw-bold ls-1">
                         <i class="clock-icon fas fa-clock me-1"></i> {{ date('d M - h:i A') }}
                    </div>
                    <form name="endDayForm" id="endDayForm" class="text-start">
                        @csrf
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        <div class="mb-3">
                            <label class="form-label fw-bold small uppercase">How was your Day? <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control border-0 bg-light rounded-3" rows="4" placeholder="Briefly describe your progress..."></textarea>
                        </div>
                    </form>
                    <div class="d-grid mt-4">
                        <button type="button" class="btn btn-danger btn-lg rounded-pill fw-bold" id="logoutBtn">Punch Out</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
