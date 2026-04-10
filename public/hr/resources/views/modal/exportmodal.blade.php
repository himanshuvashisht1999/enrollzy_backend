 <div class="modal fade" id="Generate_Report" tabindex="-1" role="dialog" aria-labelledby="Generate_Report"
     aria-hidden="true">
     <div class="modal-dialog" role="document" style="max-width: 720px;">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Export Your Desired Data</h5>
                 <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">×</span>
                 </button>
             </div>
             <div class="modal-body">
                 <div class="row">
                     <a href="{{ route('admin.export.product') }}" class="btn btn-primary btn-sm m-2"> Product</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Customer</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Author</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Publisher</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Category</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Sub Category</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Sub Sub Category</a>
                     <a href="#" class="btn btn-primary btn-sm m-2"> Brand</a>

                 </div>
                 </form>
             </div>
             <div class="modal-footer">
                 <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                 <a class="btn btn-primary" id="AddSUB_ServiceBtn" href="javascript:;"> Add </a>
             </div>
         </div>
     </div>
 </div>
 <!-- Content Row -->
