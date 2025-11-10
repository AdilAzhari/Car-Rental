<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceived;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        // Notify vehicle owner
        if ($payment->booking && $payment->booking->vehicle && $payment->booking->vehicle->owner) {
            $payment->booking->vehicle->owner->notify(new PaymentReceived($payment));
        }

        // Notify customer
        if ($payment->booking && $payment->booking->user) {
            $payment->booking->user->notify(new PaymentReceived($payment));
        }

        // Notify admins
        $admins = User::where('role', UserRole::ADMIN)->get();
        foreach ($admins as $admin) {
            $admin->notify(new PaymentReceived($payment));
        }
    }
}
