<?php

namespace App\Http\Traits;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Auth;

trait Email
{
    protected function sendEmail(array $data)
    {
        // Create initial log entry
        $log = EmailLog::create([
            'email' => $data['email'],
            'subject' => $data['subject'],
            'email_body' => $data['html'],
            'status' => 'pending',
            'created_by' => Auth::id()
        ]);

        // Dispatch email job
        dispatch(new SendEmailJob([
            'email' => $data['email'],
            'subject' => $data['subject'],
            'html' => $data['html'],
            'created_by' => Auth::id()
        ]));

        return true;
    }
}
