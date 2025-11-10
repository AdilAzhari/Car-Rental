<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingForOwner extends Notification implements ShouldQueue
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
            ->subject('New Booking for Your Vehicle - '.$this->booking->vehicle->make.' '.$this->booking->vehicle->model)
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('You have received a new booking for your vehicle!')
            ->line('**Booking Details:**')
            ->line('Vehicle: '.$this->booking->vehicle->make.' '.$this->booking->vehicle->model)
            ->line('Renter: '.$this->booking->renter->first_name.' '.$this->booking->renter->last_name)
            ->line('Dates: '.$this->booking->start_date->format('M d, Y').' - '.$this->booking->end_date->format('M d, Y'))
            ->line('Total Amount: $'.number_format($this->booking->total_amount, 2))
            ->line('Payment Method: '.ucfirst(is_string($this->booking->payment_method) ? $this->booking->payment_method : $this->booking->payment_method->value))
            ->line('Status: '.ucfirst((string) $this->booking->status->value))
            ->action('View Booking Details', config('app.frontend_url').'/owner/bookings/'.$this->booking->id)
            ->line('Please prepare your vehicle for the rental period.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'vehicle_name' => $this->booking->vehicle->make.' '.$this->booking->vehicle->model,
            'renter_name' => $this->booking->renter->first_name.' '.$this->booking->renter->last_name,
            'total_amount' => $this->booking->total_amount,
            'start_date' => $this->booking->start_date,
            'end_date' => $this->booking->end_date,
        ];
    }
}
