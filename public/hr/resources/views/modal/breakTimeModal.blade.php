 <div class="modal fade" id="breakTimeModal" tabindex="-1" role="dialog" aria-labelledby="breakTimeModal"
     aria-hidden="true">
     <div class="modal-dialog" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Break Time </h5>
                 <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">×</span>
                 </button>
             </div>
             <div class="modal-body">
                 <div class="date-time text-center">
                     <h5><b> <i class="clock-icon fas fa-clock"></i> {{ date('d M - h:i A') }}</b></h5>
                 </div>
                 <form name="breakForm" id="breakForm">
                     @csrf
                     <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                     <div class="form-group">
                         <label for="workingFrom">Break for <span class="text-danger">*</span></label>
                         <select class="form-control" id="break_for" name="break_for">
                             <option value="lunch">Lunch Break</option>
                             <option value="personal">Peronal work</option>
                         </select>
                     </div>
                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                 <a href="javascript:;" class="btn btn-primary" id="breakBtn">Go</a>
             </div>
         </div>
     </div>
 </div>
 <!-- Content Row -->
