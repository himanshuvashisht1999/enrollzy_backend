<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        $data = "";
        return view('project.whatsapp.index', compact('data'));
    }
    public function sendMessage(Request $request)
    {
        $mobileNumber = $request->mobile;
        $messageSend = $request->message;
        $client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    
        $to = '+91'.$mobileNumber ;
            if (!$to) {
                return response()->json(['error' => 'Recipient phone number is required'], 400);
            }
        $to = 'whatsapp:' . $to;
        try {
            $responseMessage = $messageSend;
            $client->messages->create(
                $to,
                [
                    'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_NUMBER'), 
                    'body' => $responseMessage,
                ]
            );
            return response()->json(['success' => 'Message sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }


        $data = "";
        return view('Project.whatsapp.index', compact('data'));
    }

}
