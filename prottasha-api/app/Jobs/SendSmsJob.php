<?php

namespace App\Jobs;

use App\Http\Controllers\Common\SmsController;
use App\Models\SmsLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 20;

    public function __construct(
        protected readonly string $number,
        protected readonly string $message
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find the pending log entry
        $log = SmsLog::where('number', $this->number)
            ->where('sms_body', $this->message)
            ->where('status', 'pending')
            ->latest()
            ->first();

        try {
            $response = $this->sendSms();

            $log?->update([
                'status' => $response ? 'sent' : 'failed',
                'return_message' => $response,
                'error_message' => $response ? null : 'Failed to send SMS'
            ]);

            if (!$response) {
                throw new \Exception('Failed to send SMS');
            }
        } catch (\Exception $e) {
            $log?->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Send SMS based on the number prefix
     */
    private function sendSms(): mixed
    {
        $prefix = substr($this->number, 0, 3);

        if ($prefix === '880' && strlen($this->number) > 10) {
            return SmsController::sendTo([
                'number' => $this->number,
                'msg' => $this->message
            ]);
        }

        return SmsController::sendToAws([
            'number' => $this->number,
            'msg' => $this->message
        ]);
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
        return 'sms';
    }
}
