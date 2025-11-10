<?php

namespace App\Http\Middleware;

use App\Exceptions\BookingException;
use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BookingOwnershipMiddleware
{
    /**
     * @throws BookingException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bookingId = $request->route('booking') ?? $request->route('id') ?? $request->booking_id;

        if (! $bookingId) {
            throw BookingException::unauthorized(0, auth()->id());
        }

        $booking = Booking::with(['vehicle'])->find($bookingId);

        if (! $booking) {
            throw BookingException::unauthorized($bookingId, auth()->id());
        }

        $user = auth()->user();

        // Check if user is the renter, vehicle owner, or admin
        $isAuthorized = $booking->renter_id === $user->id
            || $booking->vehicle->owner_id === $user->id
            || $user->hasRole('admin');

        if (! $isAuthorized) {
            throw BookingException::unauthorized($bookingId, $user->id);
        }

        // Add booking to request for easy access in controllers
        $request->merge(['_booking' => $booking]);

        return $next($request);
    }
}
