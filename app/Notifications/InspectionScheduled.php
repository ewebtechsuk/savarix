<?php

namespace App\Notifications;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;

class InspectionScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Inspection $inspection)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'nexmo'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inspection Scheduled')
            ->line('An inspection has been scheduled for property #' . $this->inspection->property_id)
            ->line('Scheduled at: ' . $this->inspection->scheduled_at->toDayDateTimeString());
    }

    public function toNexmo($notifiable): NexmoMessage
    {
        return (new NexmoMessage)
            ->content('Inspection scheduled at ' . $this->inspection->scheduled_at->toDayDateTimeString());
    }
}
