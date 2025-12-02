<?php

namespace App\Notifications;

use App\Models\BillingRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BillingNotification extends Notification
{
    use Queueable;

    protected $billingRecord;
    protected $type;

    public function __construct(BillingRecord $billingRecord, string $type = 'generated')
    {
        $this->billingRecord = $billingRecord;
        $this->type = $type;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Hello ' . $notifiable->name);

        if ($this->type === 'generated') {
            $message->line('Your new bill has been generated.')
                   ->line('Amount: ' . $this->billingRecord->bill_amount . ' ' . $this->billingRecord->subscription->currency)
                   ->line('Due Date: ' . $this->billingRecord->payment_due_date->format('Y-m-d'))
                   ->action('View Bill', url('/billing/view/' . $this->billingRecord->billing_id));
        } elseif ($this->type === 'reminder') {
            $message->line('This is a reminder about your upcoming payment.')
                   ->line('Amount Due: ' . $this->billingRecord->bill_amount . ' ' . $this->billingRecord->subscription->currency)
                   ->line('Due Date: ' . $this->billingRecord->payment_due_date->format('Y-m-d'))
                   ->action('Pay Now', url('/billing/pay/' . $this->billingRecord->billing_id));
        } elseif ($this->type === 'overdue') {
            $message->line('Your payment is overdue.')
                   ->line('Amount Due: ' . $this->billingRecord->bill_amount . ' ' . $this->billingRecord->subscription->currency)
                   ->line('Due Date: ' . $this->billingRecord->payment_due_date->format('Y-m-d'))
                   ->line('Please make the payment as soon as possible to avoid service interruption.')
                   ->action('Pay Now', url('/billing/pay/' . $this->billingRecord->billing_id));
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'billing_id' => $this->billingRecord->billing_id,
            'amount' => $this->billingRecord->bill_amount,
            'due_date' => $this->billingRecord->payment_due_date,
            'type' => $this->type
        ];
    }

    protected function getSubject(): string
    {
        return match ($this->type) {
            'generated' => 'New Bill Generated',
            'reminder' => 'Payment Reminder',
            'overdue' => 'Payment Overdue',
            default => 'Billing Notification'
        };
    }
}
