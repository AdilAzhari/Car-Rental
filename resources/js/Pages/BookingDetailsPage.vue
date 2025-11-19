<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <button @click="goBack" class="mr-4 p-2 text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </button>
                        <h1 class="text-2xl font-bold text-gray-900">Booking Details</h1>
                    </div>
                    <nav class="flex space-x-4">
                        <a href="/my-bookings" class="text-gray-600 hover:text-gray-900">My Bookings</a>
                        <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Booking Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Booking Status Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">Booking #{{ booking.id }}</h2>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatusClass(booking.status)"
                            >
                                {{ getStatusText(booking.status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Created:</span>
                                <div class="font-medium text-gray-900">{{ formatDate(booking.created_at) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Payment Status:</span>
                                <div class="font-medium" :class="getPaymentStatusClass(booking.payment_status)">
                                    {{ getPaymentStatusText(booking.payment_status) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Details Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Details</h3>
                        <div class="flex items-start space-x-4">
                            <!-- Car Image -->
                            <div class="flex-shrink-0 w-32 h-24 bg-gray-200 rounded-lg overflow-hidden">
                                <img
                                    v-if="booking.vehicle?.images?.length > 0"
                                    :src="booking.vehicle.images[0].image_url"
                                    :alt="`${booking.vehicle.make} ${booking.vehicle.model}`"
                                    class="w-full h-full object-cover"
                                >
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Vehicle Info -->
                            <div class="flex-1">
                                <h4 class="text-xl font-semibold text-gray-900 mb-2">
                                    {{ booking.vehicle?.make }} {{ booking.vehicle?.model }}
                                </h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Year:</span>
                                        <div class="font-medium text-gray-900">{{ booking.vehicle?.year }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Transmission:</span>
                                        <div class="font-medium text-gray-900">{{ booking.vehicle?.transmission }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Seats:</span>
                                        <div class="font-medium text-gray-900">{{ booking.vehicle?.seats }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Fuel Type:</span>
                                        <div class="font-medium text-gray-900">{{ booking.vehicle?.fuel_type }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Details Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rental Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <span class="text-gray-500 text-sm">Pickup Date & Time:</span>
                                    <div class="font-medium text-gray-900">{{ formatDateTime(booking.start_date) }}</div>
                                </div>
                                <div class="mb-4">
                                    <span class="text-gray-500 text-sm">Pickup Location:</span>
                                    <div class="font-medium text-gray-900">{{ booking.pickup_location }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <span class="text-gray-500 text-sm">Return Date & Time:</span>
                                    <div class="font-medium text-gray-900">{{ formatDateTime(booking.end_date) }}</div>
                                </div>
                                <div class="mb-4">
                                    <span class="text-gray-500 text-sm">Return Location:</span>
                                    <div class="font-medium text-gray-900">{{ booking.dropoff_location }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">Duration:</span>
                                <div class="font-medium text-gray-900">
                                    {{ booking.days }} {{ booking.days === 1 ? 'day' : 'days' }}
                                </div>
                            </div>
                        </div>
                        <div v-if="booking.special_requests" class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-gray-500 text-sm">Special Requests:</span>
                            <div class="font-medium text-gray-900 mt-1">{{ booking.special_requests }}</div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Payment Summary -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Daily Rate:</span>
                                <span class="font-medium">${{ booking.vehicle?.daily_rate || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Duration:</span>
                                <span class="font-medium">{{ booking.days }} {{ booking.days === 1 ? 'day' : 'days' }}</span>
                            </div>
                            <div v-if="booking.deposit_amount > 0" class="flex justify-between">
                                <span class="text-gray-600">Deposit:</span>
                                <span class="font-medium">${{ booking.deposit_amount }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                                    <span class="text-lg font-semibold text-gray-900">${{ booking.total_amount }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Payment Method:</span>
                                <span class="font-medium text-gray-900 capitalize">{{ booking.payment_method || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div v-if="booking.payments && booking.payments.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h3>
                        <div class="space-y-3">
                            <div
                                v-for="payment in booking.payments"
                                :key="payment.id"
                                class="border border-gray-200 rounded-lg p-3"
                            >
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-900">${{ payment.amount }}</span>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getPaymentStatusClass(payment.status)"
                                    >
                                        {{ getPaymentStatusText(payment.status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ formatDate(payment.created_at) }} • {{ payment.payment_method }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Contact -->
                    <div v-if="booking.vehicle?.owner" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Owner</h3>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="text-gray-500">Name:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ booking.vehicle.owner.name }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-500">Email:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ booking.vehicle.owner.email }}</span>
                            </div>
                            <div v-if="booking.vehicle.owner.phone" class="text-sm">
                                <span class="text-gray-500">Phone:</span>
                                <span class="font-medium text-gray-900 ml-1">{{ booking.vehicle.owner.phone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            <button
                                v-if="canPay"
                                @click="goToPayment"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Pay Now
                            </button>
                            <button
                                v-if="canCancel"
                                @click="cancelBooking"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Cancel Booking
                            </button>
                            <button
                                @click="downloadReceipt"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Download Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
    }
})

// Computed properties
const canCancel = computed(() => {
    return ['pending', 'pending_payment', 'confirmed'].includes(props.booking.status)
})

const canPay = computed(() => {
    return ['pending', 'pending_payment'].includes(props.booking.status) &&
           ['unpaid', 'pending'].includes(props.booking.payment_status)
})

// Methods
const goBack = () => {
    router.visit('/my-bookings')
}

const goToPayment = () => {
    router.visit(`/booking/${props.booking.id}/payment`)
}

const getStatusClass = (status) => {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'pending_approval': 'bg-yellow-100 text-yellow-800',
        'pending_payment': 'bg-blue-100 text-blue-800',
        'confirmed': 'bg-green-100 text-green-800',
        'ongoing': 'bg-indigo-100 text-indigo-800',
        'completed': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800',
        'payment_failed': 'bg-red-100 text-red-800'
    }
    return statusClasses[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
    const statusTexts = {
        'pending': 'Pending',
        'pending_approval': 'Pending Approval',
        'pending_payment': 'Pending Payment',
        'confirmed': 'Confirmed',
        'ongoing': 'Ongoing',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
        'payment_failed': 'Payment Failed'
    }
    return statusTexts[status] || status
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const cancelBooking = () => {
    if (confirm('Are you sure you want to cancel this booking?')) {
        router.post(`/my-bookings/${props.booking.id}/cancel`, {}, {
            onSuccess: () => {
                alert('Booking cancelled successfully')
            },
            onError: () => {
                alert('Failed to cancel booking. Please try again.')
            }
        })
    }
}

const downloadReceipt = () => {
    // This would typically download a PDF receipt
    window.open(`/my-bookings/${props.booking.id}/receipt`, '_blank')
}
</script>
