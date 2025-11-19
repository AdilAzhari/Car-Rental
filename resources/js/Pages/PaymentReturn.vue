<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <!-- Payment Status Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4"
                     :class="isSuccessful ? 'bg-green-100' : 'bg-yellow-100'">
                    <svg v-if="isSuccessful" class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg v-else class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ isSuccessful ? 'Payment Successful!' : 'Payment Processing' }}
                </h1>

                <!-- Message -->
                <p class="text-gray-600 mb-6">{{ message }}</p>

                <!-- Booking Details -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="text-left">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-500">Booking ID:</span>
                            <span class="text-sm font-medium text-gray-900">#{{ booking.id }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-500">Vehicle:</span>
                            <span class="text-sm font-medium text-gray-900">{{ booking.vehicle?.make }} {{ booking.vehicle?.model }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-500">Total Amount:</span>
                            <span class="text-sm font-medium text-gray-900">${{ booking.total_amount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Payment Status:</span>
                            <span class="text-sm font-medium" :class="getPaymentStatusClass(booking.payment_status)">
                                {{ getPaymentStatusText(booking.payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <button
                        @click="viewBooking"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        View Booking Details
                    </button>
                    <button
                        @click="goToBookings"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        View All Bookings
                    </button>
                </div>

                <!-- Additional Info -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        If you have any questions about your booking, please contact our support team.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

// Props
const props = defineProps({
    booking: {
        type: Object,
        required: true
    },
    message: {
        type: String,
        default: 'Payment processing completed.'
    }
})

// Computed properties
const isSuccessful = computed(() => {
    return props.booking.payment_status === 'paid' || props.booking.status === 'confirmed'
})

// Methods
const viewBooking = () => {
    router.visit(`/my-bookings/${props.booking.id}`)
}

const goToBookings = () => {
    router.visit('/my-bookings')
}

const getPaymentStatusClass = (status) => {
    const statusClasses = {
        'unpaid': 'text-yellow-600',
        'paid': 'text-green-600',
        'refunded': 'text-blue-600',
        'failed': 'text-red-600'
    }
    return statusClasses[status] || 'text-gray-600'
}

const getPaymentStatusText = (status) => {
    const statusTexts = {
        'unpaid': 'Unpaid',
        'paid': 'Paid',
        'refunded': 'Refunded',
        'failed': 'Failed'
    }
    return statusTexts[status] || status
}
</script>