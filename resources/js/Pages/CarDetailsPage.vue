<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Enhanced Header -->
        <header class="bg-white shadow-lg border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <!-- Left Section: Brand + Navigation -->
                    <div class="flex items-center space-x-8">
                        <!-- Brand -->
                        <div class="flex items-center">
                            <img
                                src="/images/logo.jpg"
                                alt="SENTIENTS A.I Logo"
                                class="w-10 h-10 rounded-xl object-cover shadow-md mr-3"
                                @error="handleImageError"
                            />
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">SENTIENTS A.I</h1>
                                <p class="text-xs text-gray-500">Car Rental System</p>
                            </div>
                        </div>

                        <!-- Navigation Breadcrumb -->
                        <nav class="hidden md:flex items-center space-x-2 text-sm">
                            <button @click="goToHome" class="text-gray-500 hover:text-blue-600 transition-colors">
                                Home
                            </button>
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <button @click="goBack" class="text-gray-500 hover:text-blue-600 transition-colors">
                                Cars
                            </button>
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-900 font-medium">
                                {{ car ? `${car.make} ${car.model}` : 'Vehicle Details' }}
                            </span>
                        </nav>
                    </div>

                    <!-- Center: Back Button (Mobile) -->
                    <button @click="goBack" class="md:hidden flex items-center text-gray-600 hover:text-blue-600 transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </button>

                    <!-- Right Section: Actions -->
                    <div class="flex items-center space-x-3">
                        <!-- Contact/Support -->
                        <button class="hidden sm:flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-sm">Support</span>
                        </button>

                        <!-- Auth Buttons -->
                        <div class="flex items-center space-x-2">
                            <a href="/login" class="text-gray-600 hover:text-blue-600 transition-colors px-3 py-2 text-sm font-medium">
                                Sign In
                            </a>
                            <a href="/register" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-sm">
                                Sign Up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse">
                <div class="h-96 bg-gray-200 rounded-lg mb-6"></div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div class="h-8 bg-gray-200 rounded mb-4"></div>
                        <div class="h-4 bg-gray-200 rounded mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    </div>
                    <div>
                        <div class="h-64 bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Details -->
        <div v-else-if="car" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Enhanced Image Carousel -->
            <div class="mb-8">
                <div class="relative">
                    <!-- Main Carousel -->
                    <div class="relative h-96 bg-gray-200 rounded-lg overflow-hidden group">
                        <div v-if="allImages.length > 0" class="relative w-full h-full">
                            <img
                                :src="getImageUrl(allImages[currentImageIndex])"
                                :alt="`${car.make} ${car.model}`"
                                class="w-full h-full object-cover cursor-pointer transition-opacity duration-300"
                                loading="eager"
                                decoding="async"
                                @click="openImageModal"
                            >

                            <!-- Image Counter -->
                            <div class="absolute top-4 right-4 bg-black bg-opacity-60 text-white px-3 py-1 rounded-full text-sm">
                                {{ currentImageIndex + 1 }} / {{ allImages.length }}
                            </div>

                            <!-- Navigation Arrows -->
                            <button v-if="allImages.length > 1"
                                    @click="previousImage"
                                    class="absolute left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded-full transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button v-if="allImages.length > 1"
                                    @click="nextImage"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded-full transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <div v-else class="flex items-center justify-center h-full text-gray-400">
                            <div class="text-center">
                                <svg class="w-24 h-24 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-lg">No images available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnail Strip -->
                    <div v-if="allImages.length > 1" class="flex space-x-2 mt-4 overflow-x-auto pb-2">
                        <button v-for="(image, index) in allImages"
                                :key="index"
                                @click="currentImageIndex = index"
                                :class="currentImageIndex === index ? 'ring-2 ring-blue-500' : 'hover:ring-2 hover:ring-gray-300'"
                                class="flex-shrink-0 w-20 h-16 bg-gray-200 rounded-lg overflow-hidden transition-all">
                            <img :src="getImageUrl(image)" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover">
                        </button>
                    </div>

                    <!-- Dots Indicator (for mobile) -->
                    <div v-if="allImages.length > 1" class="flex justify-center mt-4 space-x-2 md:hidden">
                        <button v-for="(image, index) in allImages"
                                :key="index"
                                @click="currentImageIndex = index"
                                :class="currentImageIndex === index ? 'bg-blue-500' : 'bg-gray-300'"
                                class="w-2 h-2 rounded-full transition-colors">
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Car Header -->
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ car.make }} {{ car.model }}</h1>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-gray-600">{{ car.year }}</span>
                            <span class="text-gray-600">•</span>
                            <span class="text-gray-600">{{ car.location }}</span>
                            <div v-if="car.average_rating > 0" class="flex items-center">
                                <span class="text-gray-600">•</span>
                                <div class="flex text-yellow-400 ml-2">
                                    <svg v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= car.average_rating ? 'fill-current' : 'text-gray-300'" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                                <span class="ml-1 text-sm text-gray-600">{{ car.average_rating.toFixed(1) }} ({{ car.total_reviews }} reviews)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Car Specifications -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Specifications</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-gray-600">Transmission:</span>
                                <span class="ml-1 font-medium">{{ car.transmission }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-gray-600">Fuel:</span>
                                <span class="ml-1 font-medium">{{ car.fuel_type }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v3a3 3 0 01-3 3z" />
                                </svg>
                                <span class="text-gray-600">Seats:</span>
                                <span class="ml-1 font-medium">{{ car.seats }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                                <span class="text-gray-600">Doors:</span>
                                <span class="ml-1 font-medium">{{ car.doors }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600">Mileage:</span>
                                <span class="ml-1 font-medium">{{ car.mileage?.toLocaleString() }} km</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600">Insurance:</span>
                                <span class="ml-1 font-medium">{{ car.insurance_included ? 'Included' : 'Not Included' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="car.description" class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                        <p class="text-gray-700 leading-relaxed">{{ car.description }}</p>
                    </div>

                    <!-- Features -->
                    <div v-if="car.features && car.features.length" class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="feature in car.features" :key="feature" class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-700">{{ feature }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Info -->
                    <div v-if="car.owner" class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Owner</h2>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium">{{ car.owner.name.charAt(0) }}</span>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">{{ car.owner.name }}</p>
                                <p class="text-sm text-gray-600">Member since {{ new Date(car.owner.created_at).getFullYear() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div v-if="car.reviews && car.reviews.length" class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviews</h2>
                        <div class="space-y-4">
                            <div v-for="review in car.reviews.slice(0, 3)" :key="review.id" class="border-b border-gray-200 pb-4 last:border-b-0">
                                <div class="flex items-center mb-2">
                                    <div class="flex text-yellow-400">
                                        <svg v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= review.rating ? 'fill-current' : 'text-gray-300'" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <span class="ml-2 font-medium text-gray-900">{{ review.renter?.name }}</span>
                                    <span class="ml-2 text-sm text-gray-500">{{ new Date(review.created_at).toLocaleDateString() }}</span>
                                </div>
                                <p class="text-gray-700">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Policies -->
                    <div v-if="car.terms_and_conditions" class="bg-white p-6 rounded-lg shadow-sm">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Terms & Conditions</h2>
                        <div class="prose prose-sm text-gray-700">
                            <p>{{ car.terms_and_conditions }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-lg shadow-sm sticky top-8 border border-gray-100">
                        <div class="mb-6">
                            <div class="flex items-baseline">
                                <span class="text-3xl font-bold text-gray-900">RM {{ car.daily_rate }}</span>
                                <span class="text-gray-600 ml-2">/ day</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">Malaysian Ringgit (MYR)</div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input
                                    v-model="bookingForm.start_date"
                                    type="date"
                                    :min="tomorrow"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    @change="calculateTotal"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input
                                    v-model="bookingForm.end_date"
                                    type="date"
                                    :min="bookingForm.start_date || tomorrow"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    @change="calculateTotal"
                                >
                            </div>
                        </div>

                        <div v-if="bookingDays > 0" class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>RM {{ car.daily_rate }} x {{ bookingDays }} {{ bookingDays === 1 ? 'day' : 'days' }}</span>
                                <span>RM {{ totalPrice.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between items-center font-bold text-gray-900 text-lg">
                                <span>Total</span>
                                <span>RM {{ totalPrice.toFixed(2) }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Taxes and fees included</div>
                        </div>

                        <button
                            @click="goToReservation"
                            :disabled="!canBook"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-3 px-4 rounded-lg font-medium transition-colors duration-200"
                        >
                            {{ !car.is_available ? 'Unavailable' : !bookingForm.start_date || !bookingForm.end_date ? 'Select Dates' : 'Reserve Now' }}
                        </button>

                        <p class="text-xs text-gray-500 mt-2 text-center">You won't be charged yet</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Car not found</h3>
            <p class="mt-1 text-sm text-gray-500">The car you're looking for doesn't exist or has been removed.</p>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" @click="closeImageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="max-w-4xl max-h-4xl p-4">
                <img :src="getImageUrl(selectedImage)" :alt="car?.make" class="max-w-full max-h-full object-contain">
            </div>
            <button @click="closeImageModal" class="absolute top-4 right-4 text-white hover:text-gray-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

// Props
const props = defineProps({
    id: [String, Number]
})

// Reactive data
const car = ref(null)
const loading = ref(true)
const error = ref(false)
const selectedImage = ref('')
const showImageModal = ref(false)
const currentImageIndex = ref(0)

const bookingForm = ref({
    start_date: '',
    end_date: ''
})

// Computed
const tomorrow = computed(() => {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    return tomorrow.toISOString().split('T')[0]
})

const bookingDays = computed(() => {
    if (!bookingForm.value.start_date || !bookingForm.value.end_date) return 0
    const start = new Date(bookingForm.value.start_date)
    const end = new Date(bookingForm.value.end_date)
    return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
})

const totalPrice = computed(() => {
    if (!car.value || bookingDays.value === 0) return 0
    return car.value.daily_rate * bookingDays.value
})

const canBook = computed(() => {
    return car.value?.is_available &&
           bookingForm.value.start_date &&
           bookingForm.value.end_date &&
           bookingDays.value > 0
})

// Combine all images for carousel
const allImages = computed(() => {
    if (!car.value) return []

    const images = []

    // Add featured image first
    if (car.value.featured_image) {
        images.push(car.value.featured_image)
    }

    // Add gallery images
    if (car.value.gallery_images && Array.isArray(car.value.gallery_images)) {
        images.push(...car.value.gallery_images)
    }

    // Add database images
    if (car.value.images && Array.isArray(car.value.images)) {
        images.push(...car.value.images.map(img => img.image_path))
    }

    return images
})

// Methods
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

    // Remove leading slash if present to avoid double slashes
    const cleanPath = imagePath.startsWith('/') ? imagePath.substring(1) : imagePath

    // Construct URL for storage path
    return `/storage/${cleanPath}`
}

const fetchCar = async (carId) => {
    loading.value = true
    error.value = false
    
    try {
        const response = await axios.get(`/api/cars/${carId}`)
        car.value = response.data.data

        // Set initial image for modal (keeping backward compatibility)
        if (car.value.featured_image) {
            selectedImage.value = car.value.featured_image
        } else if (car.value.gallery_images && car.value.gallery_images.length) {
            selectedImage.value = car.value.gallery_images[0]
        } else if (car.value.images && car.value.images.length) {
            selectedImage.value = car.value.images[0].image_path
        }

        // Reset carousel index
        currentImageIndex.value = 0
        
    } catch (err) {
        console.error('Error fetching car:', err)
        error.value = true
    } finally {
        loading.value = false
    }
}

const calculateTotal = () => {
    // Trigger reactivity for computed properties
    if (bookingForm.value.start_date && bookingForm.value.end_date) {
        if (new Date(bookingForm.value.end_date) <= new Date(bookingForm.value.start_date)) {
            const startDate = new Date(bookingForm.value.start_date)
            startDate.setDate(startDate.getDate() + 1)
            bookingForm.value.end_date = startDate.toISOString().split('T')[0]
        }
    }
}

const goToReservation = () => {
    if (canBook.value) {
        const query = new URLSearchParams({
            car_id: car.value.id,
            start_date: bookingForm.value.start_date,
            end_date: bookingForm.value.end_date
        })
        
        router.visit(`/reservations/create?${query}`)
    }
}

const previousImage = () => {
    if (allImages.value.length > 0) {
        currentImageIndex.value = currentImageIndex.value === 0
            ? allImages.value.length - 1
            : currentImageIndex.value - 1

        // Update selected image for modal
        selectedImage.value = allImages.value[currentImageIndex.value]
    }
}

const nextImage = () => {
    if (allImages.value.length > 0) {
        currentImageIndex.value = currentImageIndex.value === allImages.value.length - 1
            ? 0
            : currentImageIndex.value + 1

        // Update selected image for modal
        selectedImage.value = allImages.value[currentImageIndex.value]
    }
}

const openImageModal = () => {
    // Set modal image to current carousel image
    selectedImage.value = allImages.value[currentImageIndex.value]
    showImageModal.value = true
}

const closeImageModal = () => {
    showImageModal.value = false
}

const goBack = () => {
    router.visit('/cars')
}

const goToHome = () => {
    router.visit('/')
}

// Keyboard navigation
const handleKeyPress = (event) => {
    if (showImageModal.value) return // Don't interfere with modal

    if (event.key === 'ArrowLeft') {
        previousImage()
    } else if (event.key === 'ArrowRight') {
        nextImage()
    }
}

// Lifecycle
onMounted(() => {
    const carId = props.id || window.location.pathname.split('/').pop()
    fetchCar(carId)

    // Add keyboard event listeners
    document.addEventListener('keydown', handleKeyPress)
})

onUnmounted(() => {
    // Clean up event listeners
    document.removeEventListener('keydown', handleKeyPress)
})
</script>