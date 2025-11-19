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
                        <h1 class="text-2xl font-bold text-gray-900">My Bookings</h1>
                    </div>
                    <nav class="flex space-x-4">
                        <a href="/cars" class="text-gray-600 hover:text-gray-900">Browse Cars</a>
                        <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Empty State -->
            <div v-if="bookings.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by making your first car rental reservation.</p>
                <div class="mt-6">
                    <a href="/cars" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Browse Cars
                    </a>
                </div>
            </div>

            <!-- Bookings List -->
            <div v-else class="space-y-6">
                <div
                    v-for="booking in bookings.data"
                    :key="booking.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 cursor-pointer"
                    @click="viewBooking(booking.id)"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-500">Booking ID:</span>
                                <span class="text-sm font-semibold text-gray-900">#{{ booking.id }}</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getStatusClass(booking.status)"
                                >
                                    {{ getStatusText(booking.status) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDate(booking.created_at) }}
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <!-- Car Image -->
                            <div class="flex-shrink-0 w-20 h-14 bg-gray-200 rounded-lg overflow-hidden">
                                <img
                                    v-if="booking.vehicle?.images?.length > 0"
                                    :src="booking.vehicle.images[0].image_url"
                                    :alt="`${booking.vehicle.make} ${booking.vehicle.model}`"
                                    class="w-full h-full object-cover"
                                >
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Booking Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    {{ booking.vehicle?.make }} {{ booking.vehicle?.model }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ booking.vehicle?.year }} • {{ booking.vehicle?.transmission }} • {{ booking.vehicle?.seats }} seats
                                </p>

                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Dates:</span>
                                        <div class="font-medium text-gray-900">
                                            {{ formatDate(booking.start_date) }} - {{ formatDate(booking.end_date) }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Duration:</span>
                                        <div class="font-medium text-gray-900">
                                            {{ booking.days }} {{ booking.days === 1 ? 'day' : 'days' }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total:</span>
                                        <div class="font-semibold text-gray-900 text-lg">
                                            ${{ booking.total_amount }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Payment:</span>
                                        <div class="font-medium text-gray-900 capitalize">
                                            {{ booking.payment_method || 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Arrow -->
                            <div class="flex-shrink-0 self-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="bookings.last_page > 1" class="flex items-center justify-between bg-white px-4 py-3 sm:px-6 rounded-lg border border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a
                            v-if="bookings.current_page > 1"
                            :href="bookings.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Previous
                        </a>
                        <a
                            v-if="bookings.current_page < bookings.last_page"
                            :href="bookings.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Next
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ bookings.from }}</span>
                                to
                                <span class="font-medium">{{ bookings.to }}</span>
                                of
                                <span class="font-medium">{{ bookings.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a
                                    v-if="bookings.current_page > 1"
                                    :href="bookings.prev_page_url"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                >
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a
                                    v-if="bookings.current_page < bookings.last_page"
                                    :href="bookings.next_page_url"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                >
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

// Props
defineProps({
    bookings: {
        type: Object,
        required: true
    }
})

// Methods
const goBack = () => {
    router.visit('/cars')
}

const viewBooking = (bookingId) => {
    router.visit(`/my-bookings/${bookingId}`)
}

const getStatusClass = (status) => {
    const statusClasses = {
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>