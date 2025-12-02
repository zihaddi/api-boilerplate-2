<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpirationNotification extends Notification
{
    use Queueable;

    protected $subscription;

    public function __construct(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $daysLeft = now()->diffInDays($this->subscription->end_date);
        
        return (new MailMessage)
            ->subject('Your Subscription is Expiring Soon')
            ->greeting('Hello ' . $notifiable->name)
            ->line("Your subscription will expire in {$daysLeft} days.")
            ->line("Subscription details:")
            ->line("- Plan: " . $this->subscription->plan->name)
            ->line("- End date: " . $this->subscription->end_date->format('Y-m-d'))
            ->line("To continue using our services without interruption, please renew your subscription.")
            ->action('Renew Now', url('/subscriptions/renew/' . $this->subscription->subscription_id))
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->subscription_id,
            'end_date' => $this->subscription->end_date,
            'days_left' => now()->diffInDays($this->subscription->end_date),
            'plan_id' => $this->subscription->plan_id
        ];
    }
}
