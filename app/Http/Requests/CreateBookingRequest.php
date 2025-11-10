<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string|null $payment_method
 * @property string|null $pickup_location
 * @property string|null $dropoff_location
 */
class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'car_id' => [
                'required',
                'integer',
                Rule::exists('car_rental_vehicles', 'id')->where(fn ($query) => $query->where('is_available', true)->where('status', 'published')),
            ],
            'start_date' => [
                'required',
                'date',
                'after:today',
                'before:'.now()->addYear()->format('Y-m-d'),
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                'before:'.now()->addYear()->format('Y-m-d'),
            ],
            'payment_method' => [
                'required',
                'string',
                'in:stripe,stripe_checkout,visa,credit,tng,touch_n_go,cash,bank_transfer',
            ],
            'payment_method_id' => [
                'required_if:payment_method,visa,credit,stripe',
                'string',
                'max:255',
            ],
            'pickup_location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'dropoff_location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'special_requests' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'car_id.exists' => 'The selected vehicle is not available for booking.',
            'start_date.after' => 'Booking start date must be at least tomorrow.',
            'end_date.after' => 'Booking end date must be after the start date.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_method_id.required_if' => 'Payment method ID is required for card payments.',
        ];
    }

    /**
     * Get validated data with computed fields.
     */
    public function getValidatedDataWithComputed(): array
    {
        $validated = $this->validated();

        // Add computed fields
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        $validated['duration_days'] = $startDate->diffInDays($endDate) + 1;
        $validated['renter_id'] = auth()->id();

        return $validated;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize payment method to lowercase
        if ($this->has('payment_method')) {
            $this->merge([
                'payment_method' => strtolower((string) $this->payment_method),
            ]);
        }

        // Set dropoff location to pickup location if not provided
        if ($this->pickup_location && ! $this->dropoff_location) {
            $this->merge([
                'dropoff_location' => $this->pickup_location,
            ]);
        }
    }
}
