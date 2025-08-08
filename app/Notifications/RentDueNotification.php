<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Invoice $invoice)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rent Due Reminder')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your rent invoice #' . $this->invoice->number . ' is due on ' . $this->invoice->due_date->toFormattedDateString() . '.')
            ->line('Amount due: £' . number_format($this->invoice->amount, 2))
            ->action('View Invoice', url('/invoices/' . $this->invoice->id))
            ->line('Thank you for your prompt payment.');
    }
}
