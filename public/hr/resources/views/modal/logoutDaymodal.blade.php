 <div class="modal fade" id="logoutDaymodal" tabindex="-1" role="dialog" aria-labelledby="logoutDaymodal"
     aria-hidden="true">
     <div class="modal-dialog" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Day End </h5>
                 <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">×</span>
                 </button>
             </div>
             <div class="modal-body">
                 <div class="date-time text-center">
                     <h5><b> <i class="clock-icon fas fa-clock"></i> {{ date('d M - h:i A') }}</b></h5>
                 </div>
                 <form name="endDayForm" id="endDayForm">
                     @csrf
                     <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                     <div class="form-group">
                         <label for="workingFrom">How was your Day ?<span class="text-danger">*</span></label>
                         <textarea name="comment" class="form-control"></textarea>
                     </div>
                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                 <a href="javascript:;" class="btn btn-primary" id="logoutBtn">Punch Out</a>
             </div>
         </div>
     </div>
 </div>
 <!-- Content Row -->
