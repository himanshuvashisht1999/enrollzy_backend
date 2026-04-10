<?php

namespace App\Http\Controllers\Backend\General;

use App\Models\Setting;
use App\Models\WhatsappConfiguration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function create()
    {
        $setting = Setting::get();
        $WhatsappConfiguration = WhatsappConfiguration::first();
        return view('general.setting.create', compact('setting','WhatsappConfiguration'));
    }

    public function store(Request $request)
    {
        try {
            foreach ($request->all() as $key => $value) {
                Setting::updateOrCreate(
                    ['option' => $key],
                    [
                        'value' => $value,
                    ]
                );
            }
            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings. ' . $e->getMessage());
        }
    }

    public function updatewhatsappcon(Request $request)
    {
        try {
               $data = WhatsappConfiguration::first();
               $data->update([
                'url' => 12,
                'order_message' => $request->order_confirmation,
                'cancel_message' => $request->cancel_order,
                'opt_message' => $request->login_otp,
               ]);

            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings. ' . $e->getMessage());
        }
    }
}
