<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Payment Received',
            'body' => 'Payment of RM '.number_format($this->payment->amount, 2)." received for booking #{$this->payment->booking_id}",
            'icon' => 'heroicon-o-banknotes',
            'icon_color' => 'success',
            'payment_id' => $this->payment->id,
            'booking_id' => $this->payment->booking_id,
            'amount' => $this->payment->amount,
            'actions' => [
                [
                    'label' => 'View Payment',
                    'url' => route('filament.admin.resources.payments.edit', ['record' => $this->payment->id]),
                ],
                [
                    'label' => 'View Booking',
                    'url' => route('filament.admin.resources.bookings.edit', ['record' => $this->payment->booking_id]),
                ],
            ],
        ];
    }
}
