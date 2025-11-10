<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashBookingPendingApproval extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cash Booking Pending Approval - Booking #'.$this->booking->id)
            ->greeting('Hello Admin,')
            ->line('A new cash booking has been created and requires approval.')
            ->line('**Booking Details:**')
            ->line('Booking ID: #'.$this->booking->id)
            ->line('Renter: '.$this->booking->renter->first_name.' '.$this->booking->renter->last_name.' ('.$this->booking->renter->email.')')
            ->line('Vehicle: '.$this->booking->vehicle->make.' '.$this->booking->vehicle->model)
            ->line('Owner: '.$this->booking->vehicle->owner->first_name.' '.$this->booking->vehicle->owner->last_name)
            ->line('Dates: '.$this->booking->start_date->format('M d, Y').' - '.$this->booking->end_date->format('M d, Y'))
            ->line('Total Amount: $'.number_format($this->booking->total_amount, 2))
            ->line('Payment Method: Cash')
            ->action('Review Booking', config('app.admin_url').'/bookings/'.$this->booking->id)
            ->line('Please contact the renter to arrange cash payment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'renter_name' => $this->booking->renter->first_name.' '.$this->booking->renter->last_name,
            'renter_email' => $this->booking->renter->email,
            'vehicle_name' => $this->booking->vehicle->make.' '.$this->booking->vehicle->model,
            'owner_name' => $this->booking->vehicle->owner->first_name.' '.$this->booking->vehicle->owner->last_name,
            'total_amount' => $this->booking->total_amount,
            'start_date' => $this->booking->start_date,
            'end_date' => $this->booking->end_date,
        ];
    }
}
