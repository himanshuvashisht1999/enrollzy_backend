 <div class="modal fade" id="endLunchBreak" tabindex="-1" role="dialog" aria-labelledby="endLunchBreak" aria-hidden="true">
     <div class="modal-dialog" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">End {{ $breaks->type ?? '' }} break </h5>
                 <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">×</span>
                 </button>
             </div>
             <div class="modal-body">
                 @if ($breaks)
                     <div class="date-time text-center">
                         @php
                             $startTime = Carbon\Carbon::parse($breaks->start); // Parse the break start time
                             $now = Carbon\Carbon::now(); // Get the current time
                             $duration = $now->diff($startTime); // Calculate the difference
                             $durationInMinutes = $duration->h * 60 + $duration->i; // Convert duration to minutes
                         @endphp
                         <h5>Breaks At : <b> <i class="clock-icon fas fa-clock"></i>
                                 {{ $startTime->format('d M - h:i A') }}</b></h5>
                         <h5>Now : <b> <i class="clock-icon fas fa-clock"></i> {{ $now->format('d M - h:i A') }}</b>
                         </h5>
                         <h5>Break Duration : <b> <i class="clock-icon fas fa-clock"></i>
                                 {{ $duration->h }} hours {{ $duration->i }} minutes {{ $duration->s }} seconds
                             </b></h5>

                         @if ($breaks->type == 'lunch')
                             @if ($durationInMinutes <= 30)
                                 <span class="text-success"><b>
                                         Congratulations, You finished your lunch within 30 minutes.
                                     </b></span>
                             @else
                                 <span class="text-danger"><b>
                                         Unfortunately, You exceeded your lunch break by {{ $durationInMinutes - 30 }}
                                         minutes.
                                     </b></span>
                             @endif
                         @endif

                         <form name="endLunchForm" id="endLunchForm">
                             @csrf
                             <input type="hidden" name="break_id" value="{{ $breaks->id }}">
                             <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                             @if ($breaks->type == 'lunch')
                                 @if ($durationInMinutes <= 30)
                                     <div class="form-group">
                                         <label for="lunch">How was your lunch? <span
                                                 class="text-danger">*</span></label>
                                         <select name="lunch_was" id="lunch" class="form-control" required>
                                             <option value="good">Good</option>
                                             <option value="average">Average</option>
                                             <option value="poor">Poor</option>
                                         </select>
                                     </div>
                                 @else
                                     <div class="form-group">
                                         <label for="reason">Why did you take so much time?
                                             <span class="text-danger">*</span></label>
                                         <textarea name="reason" id="reason" class="form-control" required></textarea>
                                     </div>
                                 @endif
                             @else
                                 <div class="form-group">
                                     <label for="reason">Why did you take personal break?
                                         <span class="text-danger">*</span></label>
                                     <textarea name="reason" id="reason" class="form-control" required></textarea>
                                 </div>
                             @endif
                         </form>
                     </div>
                 @endif
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                 <a href="javascript:;" class="btn btn-primary" id="endBreakBtn">End</a>
             </div>
         </div>
     </div>
 </div>
 <!-- Content Row -->
