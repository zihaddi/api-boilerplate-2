<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLog;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    public function __construct(
        protected readonly array $data
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find the pending log entry
        $log = EmailLog::where('email', $this->data['email'])
            ->where('subject', $this->data['subject'])
            ->where('status', 'pending')
            ->latest()
            ->first();

        try {
            Mail::send('emails.default', ['content' => $this->data['html']], function ($message) {
                $message->to($this->data['email'])
                    ->subject($this->data['subject']);
            });

            // Log the email
            $log?->update([
                'status' => 'sent'
            ]);
        } catch (\Exception $e) {
            // Log failed email
            $log?->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the queue connection for the job.
     */
    public function viaConnection(): string
    {
        return 'database';
    }

    /**
     * Get the queue name for the job.
     */
    public function viaQueue(): string
    {
        return 'emails';
    }
}
