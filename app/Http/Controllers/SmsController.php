<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwilioService;

class SmsController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    // Display the form
    public function create()
    {
        return view('smstest');
    }

    // Handle the form submission and send SMS
    public function send(Request $request)
    {
        $validated = $request->validate([
            'to'      => 'required',
            'message' => 'required',
        ]);

        $this->twilio->sendSms($validated['to'], $validated['message']);

        return back()->with('success', 'SMS sent successfully!');
    }
}
