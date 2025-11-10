<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationForRenter extends Notification implements ShouldQueue
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
        $mailMessage = (new MailMessage)
            ->subject('Booking Confirmation - '.$this->booking->vehicle->make.' '.$this->booking->vehicle->model)
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('Your booking has been successfully created!')
            ->line('**Booking Details:**')
            ->line('Vehicle: '.$this->booking->vehicle->make.' '.$this->booking->vehicle->model)
            ->line('Dates: '.$this->booking->start_date->format('M d, Y').' - '.$this->booking->end_date->format('M d, Y'))
            ->line('Total Amount: $'.number_format($this->booking->total_amount, 2))
            ->line('Status: '.ucfirst((string) $this->booking->status->value));

        if ($this->booking->payment_method === 'cash') {
            $mailMessage->line('**Payment Method:** Cash')
                ->line('Please contact our office to complete the payment process.')
                ->line('Your booking is pending approval and will be confirmed once payment is received.');
        } else {
            $mailMessage->line('**Payment Method:** '.ucfirst(is_string($this->booking->payment_method) ? $this->booking->payment_method : $this->booking->payment_method->value));
        }

        return $mailMessage->action('View Booking', config('app.frontend_url').'/bookings/'.$this->booking->id)
            ->line('Thank you for choosing our car rental service!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'vehicle_name' => $this->booking->vehicle->make.' '.$this->booking->vehicle->model,
            'total_amount' => $this->booking->total_amount,
            'status' => $this->booking->status,
            'start_date' => $this->booking->start_date,
            'end_date' => $this->booking->end_date,
        ];
    }
}
