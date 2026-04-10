<?php

namespace App\Http\Controllers\Backend\General;

use Exception;
use App\Models\WhatsappTemplate;
// use App\Models\CustomerCategories;
use App\Models\WhatsappMessage;
use App\Models\Users;
use App\Models\WhatsappSender;
use App\Models\CustomerCategories;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WhatsappTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:slide-banner-list', ['only' => ['store']]);
        // $this->middleware('permission:slide-banner-create', ['only' => ['store']]);
        // $this->middleware('permission:slide-banner-edit', ['only' => ['update']]);
        // $this->middleware('permission:slide-banner-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = WhatsappTemplate::where('organization_id',auth()->user()->organization_id)->get();
        return view('general.whatsapp_template.index', compact('data'));
    }

    public function create()
    {
        return view('general.whatsapp_template.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'message' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $data = $request->only([
            'name',
            'message',
            'caption',
            'status',
        ]);
        $data['created_at'] = now();
        $data['organization_id'] = auth()->user()->organization_id;
        try {
            $result = WhatsappTemplate::create($data);
            staffLog('whatsapp_template', $result->id, 'create', ' whatsapp template created');
            return redirect(route('admin.whatsapp_template.index'))->with('success', 'Whatsapp template created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage())->withInput();
        }
    }

    public function show()
    {
        // code here
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $data = WhatsappTemplate::find($id);
        if ($data) {
            return view('general.whatsapp_template.edit', compact('data'));
        }
        return redirect()->back()->with('error', 'Whatsapp Template Not found, Please refresh the page');
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $data = WhatsappTemplate::find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Whatsapp Template Not found, Please refresh the page')->withInput();
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'name' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $save_data = $request->only([
            'name',
            'message',
            'caption',
            'status',
        ]);
        $save_data['updated_at'] = now();
        $save_data['organization_id'] = auth()->user()->organization_id;
        if ($data->update($save_data)) {
            staffLog('whatsapp_template', $data->id, 'update', ' whatsapp template updated');
            return redirect(route('admin.whatsapp_template.index'))->with('success', 'Whatsapp template updated successfully');
        } else {
            return redirect()->back()->with('error', 'Something went wrong, please Try again')->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $delete_data = WhatsappTemplate::where('organization_id',auth()->user()->organization_id)->find($id);
        if ($delete_data) {
            staffLog('whatsapp_template', $id, 'delete', ' whatsapp template deleted');
            $delete_data->delete();
            return redirect()->back()->with('success', 'Whatsapp template deleted successfully');
        }
        return redirect()->back()->with('error', 'Whatsapp  template not found, please try again');
    }


    public function sendMessage($id)
    {
        $id = decrypt($id);
        $response['whatsapp_template'] = WhatsappTemplate::where('organization_id',auth()->user()->organization_id)->find($id);
        $response['categories'] = CustomerCategories::with('users')->select('id', 'name', 'status','customer_type')->withCount('users')->where('status','active')->where('organization_id',auth()->user()->organization_id)->where('parent_id',0)->get();
        // $response['categories'] = [];
        if ($response['whatsapp_template']) {
            return view('general.whatsapp_template.send_message', $response);
        }
        return redirect()->back()->with('error', 'Whatsapp Template Not found, Please refresh the page');
    }

    public function postSendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_time_gap' => 'required|integer|min:1',
            'max_time_gap' => 'required|integer|min:1',
            'batch_size' => 'required|integer|min:1',
            'batch_gap' => 'required|integer|min:1',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        $min_time_gap = (int)$request->min_time_gap;
        $max_time_gap = (int)$request->max_time_gap;
        

        $batch_size = (int)$request->batch_size;
        $batch_gap = (int)$request->batch_gap;
        $message = trim($request->message);
        $caption = trim($request->caption);
        $created_at = now(); 

        // ✅ Parse manual numbers
        $manual_numbers = [];
        if (!empty($request->numbers)) {
            $manual_numbers = preg_split('/[\s,]+/', $request->numbers);
            $manual_numbers = array_filter(array_map('trim', $manual_numbers));
        }

        // ✅ Fetch numbers from selected user categories
        $category_numbers = [];
        if (!empty($request->user_categories)) {
            $category_numbers = Users::whereIn('category_id', $request->user_categories)
                ->pluck('phone')
                ->filter()
                ->toArray();
        }

        // ✅ Merge & unique all numbers
        $all_numbers = array_unique(array_merge($manual_numbers, $category_numbers));

        if (empty($all_numbers)) {
            return redirect()->back()->with('error', 'No valid phone numbers found.')->withInput();
        }
   
        // ✅ Save sender config
        $imgName = '';
        if($request->file('image')){
            $image = $request->file('image');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "whatsapp-image-".rand()."_".time().".".$extImage;
            $destinationPath = public_path().'/assets/whatsapp';
            $image->move($destinationPath, $imgName);
        }
        $senderData = [
            'number' => implode(',', $manual_numbers),
            'min_time_gap' => $min_time_gap,
            'max_time_gap' => $max_time_gap,
            'batch_size' => $batch_size,
            'batch_gap' => $batch_gap,
            'user_categories' => json_encode($request->user_categories ?? []),
            'message' => $message,
            'image' => $imgName,
            'caption' => $caption,
            'start_time' => $request->start_time,
            'start_pausing_time' => $request->start_pausing_time,
            'end_pausing_time' => $request->end_pausing_time,
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
                'start_pausing_time' => $request->start_pausing_time,
                'end_pausing_time' => $request->end_pausing_time,
            ];

            $messageModel = WhatsappMessage::create($msgData);

            // if you have a staff log helper
            staffLog('whatsapp_message', $messageModel->id, 'create', 'WhatsApp message queued.');
        }

        return redirect()
            ->route('admin.whatsapp_template.index')
            ->with('success', 'WhatsApp messages queued for ' . count($all_numbers) . ' numbers.');
    }

    public function report(){
        $data = WhatsappMessage::where('organization_id',auth()->user()->organization_id)->orderBy('id','desc')->get();
        return view('general.whatsapp_template.report', compact('data'));
    }
    public function whatsappStop(){
        $data = WhatsappMessage::where('status','draft')->where('organization_id',auth()->user()->organization_id)->update([
            'status' => 'cancelled',
        ]);
        return redirect()->back()->with('success', 'WhatsApp messages stopped successfully!');
    }

    public function getCategoryNumbers(Request $request)
    {
        $categoryIds = $request->category_ids ?? [];

        if (empty($categoryIds)) {
            return response()->json(['numbers' => []]);
        }

        $numbers = Users::whereIn('category_id', $categoryIds)
                    ->whereNotNull('phone')
                    ->pluck('phone')
                    ->unique()
                    ->values();

        return response()->json(['numbers' => $numbers]);
    }



}
