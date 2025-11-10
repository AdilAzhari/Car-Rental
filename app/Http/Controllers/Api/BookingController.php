<?php

namespace App\Http\Controllers\Api;

use App\Actions\Booking\CreateBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        private readonly CreateBookingAction $createBookingAction
    ) {}

    /**
     * Create a new booking.
     */
    public function store(CreateBookingRequest $createBookingRequest): JsonResponse
    {
        Log::info('🚀 BOOKING REQUEST STARTED', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'request_data' => $createBookingRequest->safe()->all(),
            'ip' => $createBookingRequest->ip(),
            'user_agent' => $createBookingRequest->userAgent(),
            'timestamp' => now(),
        ]);

        $validatedData = $createBookingRequest->getValidatedDataWithComputed();

        Log::info('✅ VALIDATION PASSED', [
            'user_id' => auth()->id(),
            'validated_data' => $validatedData,
        ]);

        try {
            Log::info('🔧 CALLING CREATE BOOKING ACTION', [
                'user_id' => auth()->id(),
                'action_class' => $this->createBookingAction::class,
                'data' => $validatedData,
            ]);

            $booking = $this->createBookingAction->execute($validatedData);

            Log::info('🎉 BOOKING CREATED SUCCESSFULLY', [
                'user_id' => auth()->id(),
                'booking_id' => $booking->id,
                'booking_status' => $booking->status,
                'vehicle_id' => $booking->vehicle_id,
                'total_amount' => $booking->total_amount,
                'created_at' => $booking->created_at,
            ]);

            $response = [
                'success' => true,
                'message' => 'Booking created successfully.',
                'booking' => new BookingResource($booking),
            ];

            // Add admin contact info for cash payments
            if ($validatedData['payment_method'] === 'cash') {
                Log::info('💰 CASH PAYMENT - ADDING ADMIN CONTACT', [
                    'booking_id' => $booking->id,
                    'user_id' => auth()->id(),
                ]);

                $response['admin_contact'] = [
                    'whatsapp' => '+1234567890', // Replace with actual admin WhatsApp
                    'email' => 'admin@carrentalsystem.com', // Replace with actual admin email
                    'message' => 'Your booking is pending approval. Please contact our admin to confirm your reservation.',
                ];
                $response['status'] = 'pending_approval';
            } else {
                $response['status'] = $booking->status;
            }

            Log::info('📤 SENDING SUCCESS RESPONSE', [
                'user_id' => auth()->id(),
                'booking_id' => $booking->id,
                'response_status' => 201,
            ]);

            return response()->json($response, 201);

        } catch (ValidationException $e) {
            Log::error('❌ VALIDATION EXCEPTION', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('💥 BOOKING CREATION EXCEPTION', [
                'user_id' => auth()->id(),
                'car_id' => $validatedData['car_id'] ?? null,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create booking. Please try again later.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(int $id): BookingResource
    {
        $booking = Booking::with(['vehicle.owner', 'vehicle.images', 'renter'])
            ->where('id', $id)
            ->where(function ($query): void {
                // User can only see their own bookings or bookings for their cars
                $query->where('renter_id', auth()->id())
                    ->orWhereHas('vehicle', function ($vehicleQuery): void {
                        $vehicleQuery->where('owner_id', auth()->id());
                    });
            })
            ->firstOrFail();

        return new BookingResource($booking);
    }
}
