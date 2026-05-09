<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappMessage;
use App\Models\User;
use App\Models\WhatsappSender;
use App\Models\CustomerCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $organization_id = auth()->user()->organization_id;
        $data = WhatsappTemplate::where('organization_id', $organization_id)->get();
        return view('admin.hr.whatsapp_template.index', compact('data'));
    }

    public function create()
    {
        return view('admin.hr.whatsapp_template.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'message' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->only(['name', 'message', 'caption', 'status']);
            $data['organization_id'] = auth()->user()->organization_id;
            
            $result = WhatsappTemplate::create($data);
            
            return redirect(route('admin.hr.whatsapp_template.index'))->with('success', 'Template created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $data = WhatsappTemplate::findOrFail($id);
        return view('admin.hr.whatsapp_template.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $template = WhatsappTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'message' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $save_data = $request->only(['name', 'message', 'caption', 'status']);
            $template->update($save_data);
            
            return redirect(route('admin.hr.whatsapp_template.index'))->with('success', 'Template updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $template = WhatsappTemplate::where('organization_id', auth()->user()->organization_id)->find($id);
        if ($template) {
            $template->delete();
            return redirect()->back()->with('success', 'Template deleted successfully');
        }
        return redirect()->back()->with('error', 'Template not found');
    }

    public function sendMessage($id)
    {
        $id = decrypt($id);
        $organization_id = auth()->user()->organization_id;
        $whatsapp_template = WhatsappTemplate::where('organization_id', $organization_id)->findOrFail($id);
        
        $categories = CustomerCategories::withCount('users')
            ->where('status', 'active')
            ->where('organization_id', $organization_id)
            ->where('parent_id', 0)
            ->get();

        return view('admin.hr.whatsapp_template.send_message', compact('whatsapp_template', 'categories'));
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

        // Logic for merging numbers (checking both phone and mobile for compatibility)
        $manual_numbers = [];
        if (!empty($request->numbers)) {
            $manual_numbers = preg_split('/[\s,]+/', $request->numbers);
            $manual_numbers = array_filter(array_map('trim', $manual_numbers));
        }

        $category_numbers = [];
        if (!empty($request->user_categories)) {
            // Check for 'mobile' column first, Fallback to 'phone'
            $query = User::whereIn('category_id', $request->user_categories);
            
            // We use a raw select to try and get either column
            $category_numbers = $query->get()
                ->map(function($user) {
                    return $user->mobile ?? $user->phone ?? null;
                })
                ->filter()
                ->toArray();
        }

        $all_numbers = array_unique(array_merge($manual_numbers, $category_numbers));

        if (empty($all_numbers)) {
            return redirect()->back()->with('error', 'No valid phone numbers found.')->withInput();
        }

        try {
            $imgName = '';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imgName = "wa-" . time() . "." . $image->getClientOriginalExtension();
                $image->move(public_path('assets/whatsapp'), $imgName);
            }

            $sender = WhatsappSender::create([
                'numbers' => $all_numbers ? implode(',', $all_numbers) : '',
                'min_time_gap' => $request->min_time_gap,
                'max_time_gap' => $request->max_time_gap,
                'batch_size' => $request->batch_size,
                'batch_gap' => $request->batch_gap,
                'user_categories' => json_encode($request->user_categories ?? []),
                'message' => $request->message,
                'image' => $imgName,
                'caption' => $request->caption,
                'start_time' => $request->start_time,
                'organization_id' => auth()->user()->organization_id,
                'status' => 'active',
            ]);

            foreach ($all_numbers as $index => $num) {
                // Calculation logic preserved from original
                $time_gap = rand($request->min_time_gap, $request->max_time_gap);
                if ($index === 0) {
                    $delay = $time_gap;
                } elseif ($index % $request->batch_size === 0) {
                    $delay = $time_gap + $request->batch_gap;
                } else {
                    $delay = $time_gap;
                }

                WhatsappMessage::create([
                    'number' => $num,
                    'whatsapp_sender_id' => $sender->id,
                    'time_gap_from_previous_message' => $delay,
                    'message' => $request->message,
                    'caption' => $request->caption,
                    'status' => 'draft',
                    'organization_id' => auth()->user()->organization_id,
                    'image' => $imgName,
                    'start_time' => $request->start_time,
                ]);
            }

            return redirect()->route('admin.hr.whatsapp_template.index')
                ->with('success', count($all_numbers) . ' messages queued successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function report()
    {
        $data = WhatsappMessage::where('organization_id', auth()->user()->organization_id)
            ->with('sender')
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.hr.whatsapp_template.report', compact('data'));
    }

    public function whatsappStop()
    {
        WhatsappMessage::where('status', 'draft')
            ->where('organization_id', auth()->user()->organization_id)
            ->update(['status' => 'cancelled']);
            
        return redirect()->back()->with('success', 'Outgoing messages stopped.');
    }

    public function getCategoryNumbers(Request $request)
    {
        $categoryIds = $request->category_ids ?? [];
        if (empty($categoryIds)) return response()->json(['numbers' => []]);

        $numbers = User::whereIn('category_id', $categoryIds)
            ->get()
            ->map(function($u) { return $u->mobile ?? $u->phone; })
            ->filter()
            ->unique()
            ->values();

        return response()->json(['numbers' => $numbers]);
    }
}
