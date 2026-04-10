<?php

namespace App\Http\Controllers\Project;
use App\Http\Controllers\Controller;
use App\Models\CustomerCategories;
use App\Models\Institutes;
use App\Models\Users;
use App\Models\Admin;
use App\Models\CallingStatus;
use App\Models\CallingHistory;
use App\Models\CallingHistoryLog;
use App\Models\CallingAction;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappSender;
use App\Models\WhatsappMessage;
use App\Models\CallingManualUser;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Auth;

use Illuminate\Http\Request;

class CallingController extends Controller
{
    public function index(Request $request)
    {
        $data = "";
        $count = "";
        $used_categories = CallingHistory::where('is_done', 1)->distinct()->pluck('category_id');
            // dd($used_categories);
        
        if(Auth::guard('admin')->user()->role === 'superadmin'){

            $category = CustomerCategories::where('status','active')->with('childrenRecursive')->where('parent_id',0)->get();
            $institutes = Institutes::get();
            $templates = WhatsappTemplate::where('status','active')->get();
        }else{

            $category = CustomerCategories::where('status','active')->where('organization_id', Auth::guard('admin')->user()->organization_id)->with('childrenRecursive')->where('parent_id',0)->get();
            $institutes = Institutes::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $templates = WhatsappTemplate::where('status','active')->where('organization_id', Auth::guard('admin')->user()->organization_id)->get();

        }
        $CallingStatus = CallingStatus::where('organization_id',Auth::guard('admin')->user()->organization_id)->get();
        // $org_user_ids = Users::where('organization_id',Auth::guard('admin')->user()->organization_id)->pluck('id');
        // $ids = CallingHistory::where('is_done',0)->whereIn('user_id',$org_user_ids)->get()->pluck('id');

        if($request->group == 1){
            if ($request->user_with_out_status == 1) {
                $calling_ids = CallingHistory::where('updated_by', Auth::user()->id)->where('user_type', 'admin')->pluck('user_id');
            }else{
                $calling_ids = CallingHistory::where('updated_by', Auth::user()->id)->where('user_type', 'admin')->where('is_done',1)->pluck('user_id');
            }
            
            $data = Users::with('category.parent')->select('id','name', 'phone','category_id','country','state','city');

            if ($request->has('category') && $request->category) {
                $data->where('category_id', 'LIKE', '%' . $request->category . '%'); // Use '=' for exact matches
            }
            if ($request->has('institute') && $request->institute) {
                $data->where('institute_id', 'LIKE', '%' . $request->institute . '%'); // Use '=' for exact matches
            }
            if ($request->country) {
                $data->where('country', $request->country);
            }

            if ($request->state) {
                $data->where('state', $request->state);
            }

            if ($request->city) {
                $data->where('city', $request->city);
            }
            if ($request->sequence_mode == 1) {
                $data->whereNotIn('category_id', $used_categories);
            }
            
            // $data->whereNotIn('id', function ($query) {
            //     $query->select('user_id')
            //         ->from('calling_histories')
            //         ->where('updated_by', Auth::user()->id)
            //         ->where('user_type', 'admin');
            // });
            
            $data->whereNotIn('id',$calling_ids);
            
            
            $count = $data->count();


            if(Auth::guard('admin')->user()->role === 'superadmin'){

                $data = $data->limit(1)->get();
            }else{
                $data = $data->where('organization_id', Auth::guard('admin')->user()->organization_id)->limit(1)->get();
            }


        }else{
            $data = CallingManualUser::with('category.parent')->select('id','name', 'phone','category_id');
            $data->whereNotIn('id', function ($query) {
                $query->select('user_id')
                    ->from('calling_histories')
                    ->where('updated_by', Auth::user()->id)
                    ->where('user_type', 'manual');
            });
            $count = $data->count();
            if(Auth::guard('admin')->user()->role === 'superadmin'){

                $data = $data->limit(1)->get();
            }else{
                $data = $data->where('organization_id', Auth::guard('admin')->user()->organization_id)->limit(1)->get();
            }
        }
        $modalData = $category;
        $user_with_out_status = $request->user_with_out_status ?? '';
        
        return view('project.calling.index', compact('category','institutes','data','CallingStatus','request','count','templates','user_with_out_status'));
    }

    public function restart(Request $request){
        $calling_ids = CallingHistory::where('updated_by', Auth::user()->id)->where('category_id',$request->category)->where('user_type', 'admin')->update([ 'is_done' => 0 ]);
        return redirect()->back()->with('success', 'Restart calling Sucessfully');
    }

    public function historyOld(Request $request)
    {
        $data = "";
        $CallingStatus = CallingStatus::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        $CallingActions = CallingAction::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();

        

        if(Auth::guard('admin')->user()->role === 'superadmin'){
            $staff = Admin::get();
            $templates = WhatsappTemplate::where('status','active')->get();

        }else{
            $staff = Admin::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            $templates = WhatsappTemplate::where('status','active')->where('organization_id', Auth::guard('admin')->user()->organization_id)->get();

        }


            $data = CallingHistory::select('id','user_name', 'user_phone', 'user_type','comment','status','calling_action_id')->where('updated_by', Auth::user()->id);
            if ($request->has('callingstatus') && $request->callingstatus) {
                $data->where('reason', 'LIKE', '%' . $request->callingstatus . '%');
            }

            $fromDate = Carbon::parse($request->from)->format('Y-m-d'); // Format as 'YYYY-MM-DD'
            $toDate = Carbon::parse($request->to)->format('Y-m-d'); // Format as 'YYYY-MM-DD'
            if($request->dateInput){
                $data->where("date_required", $request->dateInput);
            }else{
            $data->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$fromDate, $toDate]);
            }
            $data = $data->where('status','active')->paginate(10)->appends([
                '_token' => $request->_token,
                'callingstatus' => $request->callingstatus,
                'from' => $request->from,
                'to' => $request->to,

            ]);

            if($request->type == 'export'){
                dd($data);
            }

        return view('project.calling.history', compact('CallingStatus','data','request','staff','CallingActions','templates'));
    }
    public function history(Request $request)
{
    $CallingStatus = CallingStatus::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
    $CallingActions = CallingAction::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();

    if(Auth::guard('admin')->user()->role === 'superadmin'){
        $staff = Admin::get();
        $templates = WhatsappTemplate::where('status','active')->get();
    } else {
        $staff = Admin::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
        $templates = WhatsappTemplate::where('status','active')
                    ->where('organization_id', Auth::guard('admin')->user()->organization_id)
                    ->get();
    }

    // Build base query (do not call ->paginate yet)
    $query = CallingHistory::with(['calling_action','user','calling_status']) // eager load relations if available
                ->select('id','user_name', 'user_phone', 'user_type','comment','status','calling_action_id','created_at','date_required','user_id','reason')
                ->where('updated_by', Auth::user()->id)
                ->where('status','active');

    // Filter by callingstatus (you previously matched 'reason' using callingstatus — adjust if needed)
    if ($request->has('callingstatus') && $request->callingstatus) {
        $query->where('reason', 'LIKE', '%' . $request->callingstatus . '%');
    }

    // Date handling (if user selected a specific dateInput, use that; otherwise between from and to)
    if ($request->dateInput) {
        // ensure date format is Y-m-d
        $date = Carbon::parse($request->dateInput)->format('Y-m-d');
        $query->where('date_required', $date);
    } else {
        // only apply from/to if provided; fallback to today range if empty (avoid errors)
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = Carbon::parse($request->from)->format('Y-m-d');
            $toDate   = Carbon::parse($request->to)->format('Y-m-d');
            $query->whereRaw("DATE(created_at) BETWEEN ? AND ?", [$fromDate, $toDate]);
        }
    }

    // If staff filter present
    if ($request->filled('staff')) {
        $query->where('updated_by', $request->staff);
    }

    // EXPORT branch: return CSV download of all matching records
    if ($request->type === 'export') {
        $rows = $query->orderBy('id','asc')->get();

        $fileName = 'calling_history_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($rows) {
            $handle = fopen('php://output', 'w');
            // Optional: add BOM for Excel on Windows to properly detect UTF-8
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row — adjust columns as you want
            fputcsv($handle, [
                'Id',
                'User Type',
                'User Name',
                'Phone',
                'Comment',
                'Status',
                'Calling Action',
                'Date Created',
                'Date Required'
            ]);

            foreach ($rows as $key=>$row) {
                $actionName = $row->calling_action ? $row->calling_action->name : ($row->calling_action_id ?? '');
                fputcsv($handle, [
                    $key + 1,
                    $row->user_type,
                    $row->user_name,
                    $row->user_phone,
                    $row->comment,
                    $row->status,
                    $actionName,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                    optional($row->date_required)->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Normal (page) branch
    $data = $query->orderBy('id', 'desc')->paginate(10)->appends([
        '_token' => $request->_token,
        'callingstatus' => $request->callingstatus,
        'from' => $request->from,
        'to' => $request->to,
        'staff' => $request->staff,
    ]);

    return view('project.calling.history', compact('CallingStatus','data','request','staff','CallingActions','templates'));
}


    public function whatsapp_message_send(Request $request){
        if($request->whatsapp_template_id && $request->user_phone){
            
            $min_time_gap = 1;
            $max_time_gap = 1;
            

            $batch_size = 1;
            $batch_gap = 1;
            $message = trim($request->message);
            $caption = trim($request->caption);
            $created_at = now(); 


            // ✅ Merge & unique all numbers
            $all_numbers = [$request->user_phone];
    
            // ✅ Save sender config
            $imgName = '';
            if($request->file('image_whatsapp')){
                $image = $request->file('image_whatsapp');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "whatsapp-image-".rand()."_".time().".".$extImage;
                $destinationPath = public_path().'/assets/whatsapp';
                $image->move($destinationPath, $imgName);
            }
            $senderData = [
                'number' => $request->user_phone,
                'min_time_gap' => $min_time_gap,
                'max_time_gap' => $max_time_gap,
                'batch_size' => $batch_size,
                'batch_gap' => $batch_gap,
                'user_categories' => json_encode([]),
                'message' => $message,
                'image' => $imgName,
                'caption' => $caption,
                'start_time' => $request->start_time,
                'organization_id' => auth()->user()->organization_id,
                'status' => 'active',
                'created_at' => $created_at,
            ];
        

            $sender = WhatsappSender::create($senderData);

            // ✅ Insert message records
            foreach ($all_numbers as $index=>$num) {
                $time_gap = rand($min_time_gap, $max_time_gap);
                $time_gap_from_previous_message = 20;
                if ($index === 0) {
                    $time_gap_from_previous_message = $time_gap;
                } 
                // If it's the start of a new batch → use batch_gap
                elseif ($index % $batch_size === 0) {
                    $time_gap_from_previous_message = $time_gap + $batch_gap;
                } 
                // Otherwise → normal time_gap
                else {
                    $time_gap_from_previous_message = $time_gap;
                }
                $msgData = [
                    'number' => $num,
                    'whatsapp_sender_id' => $sender->id,
                    'time_gap_from_previous_message' => $time_gap_from_previous_message,
                    'message' => $message,
                    'caption' => $caption,
                    'status' => 'draft',
                    'organization_id' => auth()->user()->organization_id,
                    'image' => $imgName,
                    'created_at' => $created_at,
                    'start_time' => $request->start_time,
                ];

                $messageModel = WhatsappMessage::create($msgData);
            }
        }
        return redirect()->back()->with('success', 'Whatsapp message sent Sucessfully');


    }

    public function updateStatus(Request $request, $id)
    {
        $item = CallingHistory::find($id); // Replace `YourModel` with your actual model
        if ($item) {
            $item->status = 'active';
            $item->calling_action_id = $request->status;
            $item->save();
            $CallingHistoryLog = CallingHistoryLog::create([

                'history_id' => $id,
                'log_type' => 'Updated',
                'updated_by' => Auth::user()->id,
                'status' => 'active',
                'calling_action_id' => $request->status
            ]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
    }

    public function create(Request $request)
    {
        $validator = Validator($request->all(), [
            'call_status' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        $callingImagePath = null;
        if ($request->hasFile('image')) {
            // Get the uploaded file
            $file = $request->file('image');
            
            // Generate a unique file name
            $fileName = time() . '-' . $file->getClientOriginalName();
            
            // Convert file name to lowercase and remove spaces
            $fileName = strtolower($fileName);
            $fileName = preg_replace('/\s+/', '', $fileName);
            
            // Define the destination path (public directory)
            $destinationPath = public_path('calling');
            
            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true); // Create the folder if it doesn't exist
            }
            
            // Move the file to the destination folder
            $file->move($destinationPath, $fileName);
            
            // Set the profile image path (relative to public folder)
            $callingImagePath = 'calling/' . $fileName;
        }

        $groupType = ($request->group_id == 2) ? 'manual' : 'admin';
        $CallingHistory = CallingHistory::create([
            'user_type' => $groupType,
            'user_id' => $request->user_id,
            'category_id' => $request->category,
            'institute_id' => $request->institute,
            'user_name' => $request->name,
            'user_phone' => $request->user_phone,
            'reason' => $request->call_status,
            'date_required' => $request->call_date,
            'comment' => $request->comment,
            'image' => $callingImagePath,
            'updated_by' => Auth::user()->id,
            'status' => 'Active',
        ]);
        if($CallingHistory){
            $CallingHistoryLog = CallingHistoryLog::create([

                'history_id' => $CallingHistory->id,
                'log_type' => 'Created',
                'updated_by' => Auth::user()->id,
                'status' => 'Active'
            ]);
        }
        

        if($request->whatsapp_template_id && $request->user_phone){
            
            $min_time_gap = 1;
            $max_time_gap = 1;
            

            $batch_size = 1;
            $batch_gap = 1;
            $message = trim($request->message);
            $caption = trim($request->caption);
            $created_at = now(); 


            // ✅ Merge & unique all numbers
            $all_numbers = [$request->user_phone];
    
            // ✅ Save sender config
            $imgName = '';
            if($request->file('image_whatsapp')){
                $image = $request->file('image_whatsapp');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "whatsapp-image-".rand()."_".time().".".$extImage;
                $destinationPath = public_path().'/assets/whatsapp';
                $image->move($destinationPath, $imgName);
            }
            $senderData = [
                'number' => $request->user_phone,
                'min_time_gap' => $min_time_gap,
                'max_time_gap' => $max_time_gap,
                'batch_size' => $batch_size,
                'batch_gap' => $batch_gap,
                'user_categories' => json_encode([]),
                'message' => $message,
                'image' => $imgName,
                'caption' => $caption,
                'start_time' => $request->start_time,
                'organization_id' => auth()->user()->organization_id,
                'status' => 'active',
                'created_at' => $created_at,
            ];
        

            $sender = WhatsappSender::create($senderData);

            // ✅ Insert message records
            foreach ($all_numbers as $index=>$num) {
                $time_gap = rand($min_time_gap, $max_time_gap);
                $time_gap_from_previous_message = 20;
                if ($index === 0) {
                    $time_gap_from_previous_message = $time_gap;
                } 
                // If it's the start of a new batch → use batch_gap
                elseif ($index % $batch_size === 0) {
                    $time_gap_from_previous_message = $time_gap + $batch_gap;
                } 
                // Otherwise → normal time_gap
                else {
                    $time_gap_from_previous_message = $time_gap;
                }
                $msgData = [
                    'number' => $num,
                    'whatsapp_sender_id' => $sender->id,
                    'time_gap_from_previous_message' => $time_gap_from_previous_message,
                    'message' => $message,
                    'caption' => $caption,
                    'status' => 'draft',
                    'organization_id' => auth()->user()->organization_id,
                    'image' => $imgName,
                    'created_at' => $created_at,
                    'start_time' => $request->start_time,
                ];

                $messageModel = WhatsappMessage::create($msgData);
            }
        }
        return redirect()->back()->with('success', 'Calling Data Updated Sucessfully');
    }

    public function store(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048', // Optional max size (in KB)
        ]);

        // Handle the file upload
        $file = $request->file('csv_file');
        $filePath = $file->storeAs('csvs', $file->getClientOriginalName()); // Store file in public/csvs folder
        if (($handle = fopen(storage_path('app/' . $filePath), 'r')) !== false) {
            $header = fgetcsv($handle); // Get the header row
            
            while (($data = fgetcsv($handle)) !== false) {
                // Retrieve category and institute values
                $category = $data[3]; // Assuming the fourth column is 'category'
                $institute = $data[4]; // Assuming the fifth column is 'institute'

                // Handle category
                $categoryId = null;
                if ($category != 'null') {
                    $existingCategory = CustomerCategories::where('name', $category)->first();
                    if (!$existingCategory) {
                        $newCategory = CustomerCategories::create(['name' => $category, 'customer_type' => 'Manual']);
                        $categoryId = $newCategory->id;
                    } else {
                        $categoryId = $existingCategory->id;
                    }
                }else{
                    $categoryId = "";
                }

                // Handle institute
                $instituteId = null;
                if ($institute != 'null') {
                    $existingInstitute = Institutes::where('name', $institute)->first();
                    if (!$existingInstitute) {
                        $newInstitute = Institutes::create(['name' => $institute]);
                        $instituteId = $newInstitute->id;
                    } else {
                        $instituteId = $existingInstitute->id;
                    }
                }else{
                    $instituteId = "";
                }

                // Check if the user already exists by 'name' and 'phone'
                $existingUser = CallingManualUser::where('name', $data[0])
                                                ->where('phone', $data[1])
                                                ->first();

                // If user doesn't exist, insert the data
                if (!$existingUser) {
                    CallingManualUser::create([
                        'name' => $data[0],     // Assuming the first column is 'name'
                        'phone' => $data[1],    // Assuming the second column is 'phone'
                        'status' => $data[2],   // Assuming the third column is 'status'
                        'category_id' => $categoryId,
                        'institute_id' => $instituteId,
                        'organization_id' => Auth::guard('admin')->user()->organization_id,
                    ]);
                }
            }

            fclose($handle);
        }
        return redirect()->back()->with('success', 'CSV data imported successfully!');
    }

    public function calling_history_update(Request $request){
        $ids = json_decode($request->ids, true);
        $update_calling_history = CallingHistory::whereIn('id',$ids)->update([
            'reason' => $request->calling_status
        ]);

        foreach($ids as $id){
            $CallingHistoryLog = CallingHistoryLog::create([

                'history_id' => $id,
                'log_type' => 'Updated',
                'updated_by' => Auth::user()->id,
                'status' => 'Active'
            ]);
        }
            
        return redirect()->back()->with('success', 'Calling Status Updated successfully!');
    }


    public function calling_history_upload_excel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // read excel
        $path = $request->file('file')->getRealPath();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $updated = 0;
        $failed  = 0;

        // Skip header row (start from 2)
        for ($i = 2; $i <= count($rows); $i++) {

            $phone = trim($rows[$i]['A'] ?? '');
            $statusId = trim($rows[$i]['B'] ?? '');

            if (!$phone || !$statusId) {
                $failed++;
                continue;
            }

            // Update records
            $historyRows = CallingHistory::where('user_phone', $phone)->get();

            if ($historyRows->count() == 0) {
                $failed++;
                continue;
            }

            foreach ($historyRows as $row) {

                $row->update([
                    'reason' => $statusId
                ]);

                CallingHistoryLog::create([
                    'history_id' => $row->id,
                    'log_type'   => 'Bulk Excel Update',
                    'updated_by' => Auth::id(),
                    'status'     => 'Active'
                ]);

                $updated++;
            }
        }

        return back()->with('success',
            "Bulk update completed. Updated: {$updated}, Failed: {$failed}"
        );
    }

}
