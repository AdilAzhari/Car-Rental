<template>
    <AppLayout>

        <!-- Page Header -->
        <section class="bg-gradient-to-br from-blue-900 via-blue-800 to-purple-900 py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <button @click="goBack" class="flex items-center text-white/80 hover:text-white transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Details
                        </button>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4">Complete Your Reservation</h1>
                    <p class="text-xl text-white/90">Secure your vehicle booking in just a few steps</p>
                </div>
            </div>
        </section>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse space-y-6">
                <div class="h-64 bg-gray-200 rounded-lg"></div>
                <div class="h-48 bg-gray-200 rounded-lg"></div>
                <div class="h-32 bg-gray-200 rounded-lg"></div>
            </div>
        </div>

        <!-- Reservation Form -->
        <div v-else-if="car" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Car Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Car Details</h2>
                        <div class="flex space-x-4">
                            <div class="w-24 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                <img
                                    v-if="car.featured_image"
                                    :src="getImageUrl(car.featured_image)"
                                    :alt="`${car.make} ${car.model}`"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                >
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ car.make }} {{ car.model }}</h3>
                                <p class="text-gray-600 text-sm">{{ car.year }} • {{ car.transmission }} • {{ car.seats }} seats</p>
                                <p class="text-gray-600 text-sm flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ car.pickup_location || car.location }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Date Selection Calendar -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <VehicleAvailabilityCalendar
                            v-if="car"
                            :vehicle-id="car.id"
                            :daily-rate="car.daily_rate"
                            @date-selected="handleDateSelection"
                            @error="handleCalendarError"
                        />
                        <div v-if="errors.calendar" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700">{{ errors.calendar }}</p>
                        </div>
                    </div>

                    <!-- Booking Details Form -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Details</h2>

                        <form @submit.prevent="submitReservation" class="space-y-4">
                            <!-- Selected Dates Display (Read-only) -->
                            <div v-if="form.start_date && form.end_date" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h3 class="font-medium text-blue-900 mb-2">Selected Dates</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Check-in:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ formatDisplayDate(form.start_date) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Check-out:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ formatDisplayDate(form.end_date) }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm text-blue-700">
                                    Duration: {{ bookingDays }} day{{ bookingDays !== 1 ? 's' : '' }} • Total: ${{ totalPrice.toFixed(2) }}
                                </div>
                            </div>

                            <!-- Fallback Date Inputs (Hidden by default, shown if calendar fails) -->
                            <div v-else-if="showFallbackDateInputs" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        :min="tomorrow"
                                        required
                                        :class="{'border-red-300': errors.start_date}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="calculateTotal"
                                    >
                                    <p v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        :min="form.start_date || tomorrow"
                                        required
                                        :class="{'border-red-300': errors.end_date}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="calculateTotal"
                                    >
                                    <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
                                </div>
                            </div>

                            <!-- Prompt to select dates if none selected -->
                            <div v-else class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm text-yellow-800">Please select your rental dates using the calendar above to continue.</p>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                                <div class="space-y-3">
                                    <!-- Stripe Checkout - Primary Payment Option -->
                                    <label class="flex items-center p-4 border-2 border-blue-500 rounded-lg cursor-pointer bg-blue-50" :class="{'bg-blue-100': form.payment_method === 'stripe_checkout'}">
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="stripe_checkout"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        >
                                        <div class="ml-3 flex items-center justify-between w-full">
                                            <div class="flex items-center">
                                                <div class="flex space-x-1 mr-3">
                                                    <!-- Stripe logo -->
                                                    <svg class="w-12 h-8" viewBox="0 0 60 25" fill="none">
                                                        <path d="M59.5 12.5c0-6.9-5.6-12.5-12.5-12.5s-12.5 5.6-12.5 12.5 5.6 12.5 12.5 12.5 12.5-5.6 12.5-12.5z" fill="#6772E5"/>
                                                        <path d="M49.9 10.8c-.3-.7-.9-1.1-1.7-1.1-.5 0-.9.2-1.2.6v-.5h-.9v5.6h.9v-2.8c0-.9.4-1.4 1-1.4.6 0 .9.4.9 1.3v2.9h.9v-3.2c0-1.2-.5-1.4-.9-1.4zm-5.2-1.1c-.8 0-1.4.3-1.8.8v-.7h-.9v5.6h.9v-2.8c0-.9.4-1.4 1-1.4s.9.4.9 1.3v2.9h.9v-3.2c0-1.2-.5-1.4-.9-1.4zm-4.9.1c-.4 0-.8.1-1.1.4v-.3h-.9v5.6h.9v-3.4c.2-.2.5-.3.8-.3.3 0 .5.1.6.4l.8-.4c-.2-.7-.7-1-1.1-1zm-3.6-.1c-.9 0-1.5.7-1.5 1.6s.6 1.6 1.5 1.6c.4 0 .8-.1 1.1-.4l-.4-.7c-.2.2-.4.3-.7.3-.5 0-.8-.3-.8-.8s.3-.8.8-.8c.3 0 .5.1.7.3l.4-.7c-.3-.3-.7-.4-1.1-.4zm-3.1.1h-.9v3.1c0 .6-.3.9-.7.9s-.7-.3-.7-.9v-3.1h-.9v3.1c0 1.1.6 1.7 1.6 1.7s1.6-.6 1.6-1.7v-3.1zm-4.6-.1c-.9 0-1.5.7-1.5 1.6s.6 1.6 1.5 1.6 1.5-.7 1.5-1.6-.6-1.6-1.5-1.6zm0 2.4c-.4 0-.6-.4-.6-.8s.2-.8.6-.8.6.4.6.8-.2.8-.6.8zm-3.4-2.3h-.6v-.9h-.9v.9h-.4v.8h.4v2.1c0 .7.3 1.1 1 1.1.2 0 .4 0 .6-.1v-.8c-.1 0-.2.1-.3.1-.2 0-.3-.1-.3-.3v-2.1h.6v-.8zm-3.7-.1c-.4 0-.8.2-1 .5v-.4h-.9v4.4h.9v-1.6c.2.3.6.5 1 .5.8 0 1.4-.7 1.4-1.6s-.6-1.8-1.4-1.8zm-.2 2.6c-.4 0-.7-.3-.7-.8s.3-.8.7-.8.7.3.7.8-.3.8-.7.8zm-3.6-2.6c-.4 0-.8.2-1 .5v-.4h-.9v4.4h.9v-1.6c.2.3.6.5 1 .5.8 0 1.4-.7 1.4-1.6s-.6-1.8-1.4-1.8zm-.2 2.6c-.4 0-.7-.3-.7-.8s.3-.8.7-.8.7.3.7.8-.3.8-.7.8z" fill="white"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900">Secure Card Payment</div>
                                                    <div class="text-sm text-gray-600">Powered by Stripe • Instant confirmation</div>
                                                </div>
                                            </div>
                                            <div class="text-sm text-blue-600 font-semibold bg-white px-2 py-1 rounded-full">Recommended</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50" :class="{'bg-blue-50 border-blue-500': form.payment_method === 'visa'}">
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="visa"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        >
                                        <div class="ml-3 flex items-center justify-between w-full">
                                            <div class="flex items-center">
                                                <svg class="w-8 h-6 mr-3" viewBox="0 0 48 32">
                                                    <rect width="48" height="32" rx="4" fill="#1A1F71"/>
                                                    <path d="M18.5 11h-3.2l-2 10h3.2l2-10zm7.4 6.5c0-1.5-1.9-2.3-3-2.8-.7-.3-1.1-.5-1.1-.8 0-.4.4-.8 1.3-.8.7 0 1.2.2 1.6.3l.3-1.8c-.4-.1-1-.3-1.8-.3-1.9 0-3.3 1-3.3 2.4 0 1 .9 1.6 1.6 2 .7.4 1 .6 1 1s-.4.7-1.1.7c-.9 0-1.4-.3-1.8-.4l-.3 1.8c.4.2 1.2.3 2 .3 2.1-.1 3.6-1 3.6-2.5zm5.9-6.4c-.4 0-.8.2-1 .6l-3.6 8.8h2l.4-1.1h2.4l.2 1.1h1.8l-1.6-10h-1.6zm.3 7.1l1-2.7.6 2.7h-1.6zm-10.6-7.1l-1.9 6.8-.2-1c-.3-1-.6-2.1-1.9-2.6-.6-.2-1.4-.4-2.2-.4h-.1l.1.2c0 0 .8.2 1.6.6 1.2.7 1.4 1.6 1.8 3.1l1.2 4.3h2l3.1-10h-1.5z" fill="white"/>
                                                </svg>
                                                <div>
                                                    <div class="font-medium text-gray-900">Visa (Legacy)</div>
                                                    <div class="text-sm text-gray-600">Direct card entry</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50" :class="{'bg-blue-50 border-blue-500': form.payment_method === 'credit'}">
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="credit"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        >
                                        <div class="ml-3 flex items-center justify-between w-full">
                                            <div class="flex items-center">
                                                <svg class="w-8 h-6 mr-3" viewBox="0 0 48 32">
                                                    <rect width="48" height="32" rx="4" fill="#EB001B"/>
                                                    <rect x="16" y="0" width="16" height="32" fill="#FF5F00"/>
                                                    <circle cx="24" cy="16" r="12" fill="#FF5F00"/>
                                                    <circle cx="16" cy="16" r="12" fill="#EB001B"/>
                                                    <circle cx="32" cy="16" r="12" fill="#F79E1B"/>
                                                </svg>
                                                <div>
                                                    <div class="font-medium text-gray-900">Credit Card</div>
                                                    <div class="text-sm text-gray-600">Instant confirmation</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50" :class="{'bg-blue-50 border-blue-500': form.payment_method === 'cash'}">
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="cash"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        >
                                        <div class="ml-3 flex items-center justify-between w-full">
                                            <div class="flex items-center">
                                                <svg class="w-8 h-6 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                                <div>
                                                    <div class="font-medium text-gray-900">Cash Payment</div>
                                                    <div class="text-sm text-gray-600">Pay at pickup - Pending confirmation</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">{{ errors.payment_method }}</p>
                            </div>

                            <!-- Special Requests -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests (Optional)</label>
                                <textarea
                                    v-model="form.special_requests"
                                    rows="3"
                                    placeholder="Any special requests or notes for the car owner..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                            </div>

                            <!-- Terms -->
                            <div class="flex items-start">
                                <input
                                    v-model="form.agree_terms"
                                    type="checkbox"
                                    required
                                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label class="ml-3 text-sm text-gray-700">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>, and confirm that all information provided is accurate.
                                </label>
                            </div>
                            <p v-if="errors.agree_terms" class="text-sm text-red-600">{{ errors.agree_terms }}</p>

                            <!-- General error display -->
                            <div v-if="errors.general" class="mt-4 bg-red-50 border border-red-200 rounded-md p-3">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm">
                                        <p class="text-red-800 font-medium">{{ errors.general }}</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
                        
                        <!-- Price Breakdown -->
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Daily Rate</span>
                                <span class="font-medium">${{ car.daily_rate }}</span>
                            </div>
                            
                            <div v-if="bookingDays > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ bookingDays }} days</span>
                                <span class="font-medium">${{ (car.daily_rate * bookingDays).toFixed(2) }}</span>
                            </div>

                            <!-- Show payment method status -->
                            <div v-if="form.payment_method === 'cash'" class="bg-yellow-50 p-3 rounded-md">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm">
                                        <p class="text-yellow-800 font-medium">Cash Payment</p>
                                        <p class="text-yellow-700">Your booking will be pending until the owner confirms.</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="form.payment_method === 'stripe_checkout'" class="bg-blue-50 p-3 rounded-md">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm">
                                        <p class="text-blue-800 font-medium">Secure Stripe Payment</p>
                                        <p class="text-blue-700">You'll be redirected to Stripe's secure checkout page.</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="form.payment_method === 'visa' || form.payment_method === 'credit'" class="bg-green-50 p-3 rounded-md">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm">
                                        <p class="text-green-800 font-medium">Instant Confirmation</p>
                                        <p class="text-green-700">Your booking will be confirmed immediately.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total</span>
                                <span class="text-2xl font-bold text-gray-900">
                                    ${{ totalPrice.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            @click="submitReservation"
                            :disabled="submitting || !canSubmit"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-3 px-4 rounded-lg font-medium transition-colors duration-200"
                        >
                            <span v-if="submitting">Processing...</span>
                            <span v-else-if="form.payment_method === 'cash'">Request Booking</span>
                            <span v-else-if="form.payment_method === 'stripe_checkout'">Continue to Payment</span>
                            <span v-else>Confirm & Book</span>
                        </button>

                        <p class="text-xs text-gray-500 mt-2 text-center">
                            <span v-if="form.payment_method === 'cash'">No payment required now</span>
                            <span v-else-if="form.payment_method === 'stripe_checkout'">Secure payment via Stripe</span>
                            <span v-else>You'll be charged immediately</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Unable to load car details</h3>
            <p class="mt-1 text-sm text-gray-500">Please try again or contact support if the problem persists.</p>
        </div>

        <!-- Success Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg p-6 max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                <div class="text-center">
                    <svg v-if="bookingResponse?.status === 'confirmed'" class="mx-auto h-12 w-12 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="mx-auto h-12 w-12 text-yellow-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ bookingResponse?.status === 'confirmed' ? 'Booking Confirmed!' : 'Booking Pending Approval' }}
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        {{ bookingResponse?.status === 'confirmed'
                            ? 'Your vehicle reservation has been confirmed. You\'ll receive a confirmation email shortly.'
                            : bookingResponse?.admin_contact?.message || 'Your booking request has been submitted and is pending approval.' }}
                    </p>

                    <!-- Admin Contact Info for Cash Payments -->
                    <div v-if="bookingResponse?.admin_contact" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <h4 class="font-semibold text-yellow-800">Action Required</h4>
                        </div>
                        <p class="text-sm text-yellow-700 mb-3">Please contact our admin to confirm your cash payment booking:</p>

                        <div class="space-y-2">
                            <a :href="`https://wa.me/${bookingResponse.admin_contact.whatsapp.replace(/\D/g, '')}?text=Hi, I just made a booking with ID: ${bookingResult?.booking_number || bookingResult?.id}. I would like to confirm my cash payment.`"
                               target="_blank"
                               class="flex items-center justify-center bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                Contact via WhatsApp
                            </a>

                            <a :href="`mailto:${bookingResponse.admin_contact.email}?subject=Booking Confirmation - ID: ${bookingResult?.booking_number || bookingResult?.id}&body=Hi, I just made a booking with ID: ${bookingResult?.booking_number || bookingResult?.id}. I would like to confirm my cash payment.`"
                               class="flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Email
                            </a>
                        </div>

                        <div class="mt-3 pt-3 border-t border-yellow-200">
                            <p class="text-xs text-yellow-600">Admin Contact:</p>
                            <p class="text-sm font-medium text-yellow-800">{{ bookingResponse.admin_contact.whatsapp }}</p>
                            <p class="text-sm font-medium text-yellow-800">{{ bookingResponse.admin_contact.email }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3 mb-4 text-left">
                        <p class="text-sm"><strong>Booking ID:</strong> {{ bookingResult?.booking_number || `#${bookingResult?.id}` }}</p>
                        <p class="text-sm"><strong>Vehicle:</strong> {{ car?.make }} {{ car?.model }}</p>
                        <p class="text-sm"><strong>Dates:</strong> {{ form.start_date }} to {{ form.end_date }}</p>
                        <p class="text-sm"><strong>Total:</strong> ${{ totalPrice.toFixed(2) }}</p>
                        <p class="text-sm"><strong>Payment Method:</strong> {{ form.payment_method.charAt(0).toUpperCase() + form.payment_method.slice(1) }}</p>
                        <p class="text-sm"><strong>Status:</strong>
                            <span :class="bookingResponse?.status === 'confirmed' ? 'text-green-600' : 'text-yellow-600'">
                                {{ bookingResponse?.status === 'confirmed' ? 'Confirmed' : 'Pending Approval' }}
                            </span>
                        </p>
                    </div>

                    <button
                        @click="closeSuccessModal"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium"
                    >
                        {{ bookingResponse?.admin_contact ? 'Close & Contact Admin' : 'View My Bookings' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import VehicleAvailabilityCalendar from '@/Components/VehicleAvailabilityCalendar.vue'
import axios from 'axios'

// Define props
const props = defineProps({
    car: {
        type: Object,
        required: false,
        default: null
    },
    booking_params: {
        type: Object,
        required: false,
        default: () => ({})
    }
})

// Get auth user from Inertia
const page = usePage()
const isAuthenticated = computed(() => page.props.auth.user !== null)
const authUser = computed(() => page.props.auth.user)

// Reactive data
const car = ref(props.car)
const loading = ref(!props.car) // Only show loading if no car prop provided
const submitting = ref(false)
const showSuccessModal = ref(false)
const bookingResult = ref(null)
const bookingResponse = ref(null)
const errors = ref({})
const showFallbackDateInputs = ref(false)

const form = ref({
    car_id: props.car?.id || null,
    start_date: props.booking_params?.start_date || '',
    end_date: props.booking_params?.end_date || '',
    payment_method: 'stripe_checkout', // Default to Stripe Checkout
    special_requests: '',
    agree_terms: false
})

// Computed
const tomorrow = computed(() => {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    return tomorrow.toISOString().split('T')[0]
})

const bookingDays = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return 0
    const start = new Date(form.value.start_date)
    const end = new Date(form.value.end_date)
    return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
})

const totalPrice = computed(() => {
    if (!car.value || bookingDays.value === 0) return 0
    return car.value.daily_rate * bookingDays.value
})

const canSubmit = computed(() => {
    return form.value.car_id &&
           form.value.start_date &&
           form.value.end_date &&
           form.value.payment_method &&
           form.value.agree_terms &&
           bookingDays.value > 0
})

// Methods
const fetchCar = async (carId) => {
    loading.value = true
    
    try {
        const response = await axios.get(`/api/cars/${carId}`)
        car.value = response.data.data
        form.value.car_id = car.value.id
    } catch (error) {
        console.error('Error fetching car:', error)
    } finally {
        loading.value = false
    }
}

const calculateTotal = () => {
    if (form.value.start_date && form.value.end_date) {
        if (new Date(form.value.end_date) <= new Date(form.value.start_date)) {
            const startDate = new Date(form.value.start_date)
            startDate.setDate(startDate.getDate() + 1)
            form.value.end_date = startDate.toISOString().split('T')[0]
        }
    }
}

const handleDateSelection = (dateSelection) => {
    console.log('📅 Date selection from calendar:', dateSelection)
    form.value.start_date = dateSelection.startDate
    form.value.end_date = dateSelection.endDate
    errors.value.calendar = null
    errors.value.start_date = null
    errors.value.end_date = null
}

const handleCalendarError = (errorMessage) => {
    console.error('📅 Calendar error:', errorMessage)
    errors.value.calendar = errorMessage
    showFallbackDateInputs.value = true
}

const formatDisplayDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const submitReservation = async () => {
    if (!canSubmit.value || submitting.value) return

    // Check authentication first - redirect to login if not authenticated
    if (!isAuthenticated.value) {
        const currentUrl = window.location.href
        router.visit('/login', {
            data: { intended: currentUrl },
            preserveState: false
        })
        return
    }

    submitting.value = true
    errors.value = {}

    try {
        console.log('🚀 Starting booking submission...')
        console.log('📊 Current auth state:', {
            isAuthenticated: isAuthenticated.value,
            authUser: authUser.value
        })

        // Ensure we have CSRF cookie for authenticated requests
        await axios.get('/sanctum/csrf-cookie')
        console.log('🔒 CSRF cookie obtained')

        // Make sure we're authenticated before proceeding
        try {
            const authCheck = await axios.get('/api/user')
            console.log('✅ Auth check passed:', authCheck.data.name)
        } catch (authError) {
            console.log('❌ Auth check failed:', authError.response?.status, authError.response?.data)
            console.log('🔄 Redirecting to login')
            const currentUrl = window.location.href
            router.visit('/login', {
                data: { intended: currentUrl },
                preserveState: false
            })
            return
        }

        const bookingData = {
            car_id: form.value.car_id,
            start_date: form.value.start_date,
            end_date: form.value.end_date,
            payment_method: form.value.payment_method,
            special_requests: form.value.special_requests || ''
        }

        console.log('📝 Sending booking data:', bookingData)

        const response = await axios.post('/api/bookings', bookingData, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        console.log('✅ Booking response:', response.data)

        bookingResult.value = response.data.booking
        bookingResponse.value = response.data

        // Handle Stripe Checkout redirection
        if (form.value.payment_method === 'stripe_checkout') {
            console.log('🔄 Redirecting to Stripe Checkout...')
            await handleStripeCheckout(response.data.booking.id)
        } else {
            showSuccessModal.value = true
        }

    } catch (error) {
        console.error('❌ Booking error:', error)
        console.error('❌ Error details:', {
            status: error.response?.status,
            statusText: error.response?.statusText,
            data: error.response?.data,
            headers: error.response?.headers
        })

        // Handle authentication errors
        if (error.response?.status === 401 || error.response?.status === 419) {
            const currentUrl = window.location.href
            router.visit('/login', {
                data: { intended: currentUrl },
                preserveState: false
            })
            return
        }

        // Handle validation errors
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors

            // Show specific field errors
            const errorMessages = []
            for (const [field, messages] of Object.entries(error.response.data.errors)) {
                if (Array.isArray(messages)) {
                    errorMessages.push(...messages)
                } else {
                    errorMessages.push(messages)
                }
            }

            if (errorMessages.length > 0) {
                errors.value.general = errorMessages.join('. ')
            }
        } else if (error.response?.data?.message) {
            errors.value = { general: error.response.data.message }
        } else if (error.response?.status === 500) {
            errors.value = { general: 'Server error occurred. Please try again or contact support.' }
        } else if (error.response?.status === 422) {
            errors.value = { general: 'Please check your input and try again.' }
        } else {
            errors.value = { general: 'An unexpected error occurred. Please try again.' }
        }
    } finally {
        submitting.value = false
    }
}

const handleStripeCheckout = async (bookingId) => {
    try {
        console.log('🔄 Creating Stripe Checkout session for booking:', bookingId)

        // Ensure we have CSRF cookie for authenticated requests
        await axios.get('/sanctum/csrf-cookie')

        const response = await axios.post('/api/payments/checkout', {
            booking_id: bookingId
        }, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        console.log('✅ Stripe Checkout session created:', response.data)

        if (response.data.success && response.data.checkout_url) {
            console.log('🚀 Redirecting to Stripe Checkout:', response.data.checkout_url)
            // Redirect to Stripe Checkout
            window.location.href = response.data.checkout_url
        } else {
            throw new Error(response.data.message || 'Failed to create checkout session')
        }

    } catch (error) {
        console.error('❌ Stripe Checkout creation failed:', error)

        // Show error and fall back to showing success modal
        errors.value.general = error.response?.data?.message || 'Failed to redirect to payment. Please try again.'
        showSuccessModal.value = true
    } finally {
        submitting.value = false
    }
}

const goBack = () => {
    if (car.value) {
        router.visit(`/cars/${car.value.id}`)
    } else {
        router.visit('/cars')
    }
}

const closeSuccessModal = () => {
    showSuccessModal.value = false
    if (bookingResponse.value?.admin_contact) {
        // For cash payments, return to home or keep modal open for admin contact
        router.visit('/cars')
    } else {
        // For confirmed bookings, go to bookings page
        router.visit('/my-bookings')
    }
}

// Image handling functions
const getImageUrl = (imagePath) => {
    if (!imagePath) return null
    // If it's already a full URL, return as is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath
    }
    // If path already starts with /storage/, return as is
    if (imagePath.startsWith('/storage/')) {
        return imagePath
    }
    // Construct URL for storage path
    const cleanPath = imagePath.startsWith('/') ? imagePath.substring(1) : imagePath
    return `/storage/${cleanPath}`
}

const handleImageError = (event) => {
    console.log('Image failed to load:', event.target.src)
    // Hide the broken image and show placeholder instead
    event.target.style.display = 'none'
}

// Lifecycle
onMounted(async () => {
    // If no car prop provided, try to get from URL (fallback for old URLs)
    if (!props.car) {
        const urlParams = new URLSearchParams(window.location.search)
        const carId = urlParams.get('car_id')
        const startDate = urlParams.get('start_date')
        const endDate = urlParams.get('end_date')

        if (!carId) {
            router.visit('/cars')
            return
        }

        // Pre-fill form with URL parameters
        if (startDate) form.value.start_date = startDate
        if (endDate) form.value.end_date = endDate

        // Fetch car details
        await fetchCar(carId)
    }

    // Log authentication status for debugging
    console.log('Auth status:', {
        isAuthenticated: isAuthenticated.value,
        user: authUser.value
    })
})
</script>