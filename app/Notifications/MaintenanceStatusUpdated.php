<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class MaintenanceStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected MaintenanceRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'vonage'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Maintenance Request Status Updated')
            ->line('The status of your maintenance request #'.$this->request->id.' has been updated to '.$this->request->status.'.');
    }

    public function toVonage($notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->content('Maintenance request #'.$this->request->id.' status: '.$this->request->status);
    }
}

