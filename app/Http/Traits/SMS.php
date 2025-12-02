<?php

namespace App\Http\Traits;

use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Auth;

trait SMS
{
    protected function sendSMS($number, $msg)
    {
        // Create log entry
        $log = SmsLog::create([
            'number' => $number,
            'sms_body' => $msg,
            'created_by' => Auth::user() ? Auth::user()->id : null,
            'date_time' => now(),
            'created_at' => now(),
            'status_id' => 1
        ]);

        // Dispatch SMS job
        dispatch(new SendSmsJob($number, $msg));

        return true;
    }
}
