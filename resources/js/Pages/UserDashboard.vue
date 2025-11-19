<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gradient">RentCar Pro</h1>
                            <p class="text-xs text-gray-500">Premium Car Rentals</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/cars" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                            Browse Cars
                        </a>
                        <a href="/my-bookings" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                            My Bookings
                        </a>
                        <form @submit.prevent="logout" class="inline">
                            <button type="submit" class="text-gray-600 hover:text-red-600 font-medium transition-colors">
                                Logout
                            </button>
                        </form>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Dashboard Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">My Dashboard</h2>
                <p class="text-gray-600">Manage your rentals, favorites, and preferences</p>
            </div>

            <!-- Dashboard Navigation -->
            <div class="flex flex-wrap gap-2 mb-8">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'px-6 py-3 rounded-lg font-semibold transition-all duration-200',
                        activeTab === tab.id 
                            ? 'bg-blue-600 text-white shadow-lg' 
                            : 'bg-white text-gray-700 hover:bg-blue-50 border border-gray-200'
                    ]"
                >
                    <div class="flex items-center space-x-2">
                        <component :is="tab.icon" class="w-5 h-5" />
                        <span>{{ tab.name }}</span>
                    </div>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <!-- Favorites Tab -->
                <div v-if="activeTab === 'favorites'" class="p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">My Favorite Cars</h3>
                    </div>

                    <!-- Loading State -->
                    <div v-if="favoritesLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="n in 6" :key="n" class="bg-gray-100 rounded-lg h-80 animate-pulse"></div>
                    </div>

                    <!-- Favorites Grid -->
                    <div v-else-if="favorites.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <VehicleCard
                            v-for="car in favorites"
                            :key="car.id"
                            :vehicle="car"
                            @view-details="goToCarDetails"
                            @reserve-now="goToReservation"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Favorites Yet</h3>
                        <p class="text-gray-600 mb-4">Start adding cars to your favorites to see them here</p>
                        <button @click="goBack" class="btn-primary">Browse Cars</button>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div v-else-if="activeTab === 'bookings'" class="p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">My Bookings</h3>
                    </div>

                    <!-- Bookings redirect -->
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">View Your Bookings</h3>
                        <p class="text-gray-600 mb-4">Manage your rental history and current bookings</p>
                        <a href="/my-bookings" class="btn-primary">Go to My Bookings</a>
                    </div>
                </div>

                <!-- Profile Tab -->
                <div v-else-if="activeTab === 'profile'" class="p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Profile Settings</h3>
                    </div>

                    <!-- Profile Form -->
                    <div class="max-w-2xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                                <input type="text" class="input-modern" placeholder="Your full name" readonly value="Demo User">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" class="input-modern" placeholder="your@email.com" readonly value="demo@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                                <input type="tel" class="input-modern" placeholder="Your phone number" readonly value="+1 (555) 123-4567">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                                <input type="text" class="input-modern" placeholder="Your city" readonly value="New York, NY">
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-sm text-gray-600">Profile editing will be available soon.</p>
                        </div>
                    </div>
                </div>

                <!-- Search Preferences Tab -->
                <div v-else-if="activeTab === 'preferences'" class="p-8">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">Search Preferences</h3>
                    </div>

                    <!-- Coming Soon -->
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Saved Preferences</h3>
                        <p class="text-gray-600">Save your favorite search filters and preferences here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue'
import { router } from '@inertiajs/vue3'
import VehicleCard from '../Components/VehicleCard.vue'
import axios from 'axios'

// Reactive data
const activeTab = ref('favorites')
const favorites = ref([])
const favoritesLoading = ref(true)

// Tab configuration
const tabs = [
    { 
        id: 'favorites', 
        name: 'Favorites', 
        icon: () => h('svg', {
            class: 'w-5 h-5',
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
        }))
    },
    { 
        id: 'bookings', 
        name: 'Bookings', 
        icon: () => h('svg', {
            class: 'w-5 h-5',
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
        }))
    },
    { 
        id: 'profile', 
        name: 'Profile', 
        icon: () => h('svg', {
            class: 'w-5 h-5',
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        }))
    },
    { 
        id: 'preferences', 
        name: 'Preferences', 
        icon: () => h('svg', {
            class: 'w-5 h-5',
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4'
        }))
    }
]

// Methods
const loadFavorites = async () => {
    try {
        favoritesLoading.value = true
        const response = await axios.get('/api/favorites')
        favorites.value = response.data.data
    } catch (error) {
        console.error('Failed to load favorites:', error)
        if (error.response?.status === 401) {
            // User not authenticated, redirect to login
            router.visit('/login')
        }
    } finally {
        favoritesLoading.value = false
    }
}

const goToCarDetails = (car) => {
    router.visit(`/cars/${car.id}`)
}

const goToReservation = (carId) => {
    router.visit(`/cars/${carId}/reserve`)
}

const goBack = () => {
    router.visit('/')
}

const logout = async () => {
    try {
        await axios.post('/logout')
        router.visit('/cars')
    } catch (error) {
        console.error('Logout error:', error)
        // Force redirect anyway
        router.visit('/cars')
    }
}

// Lifecycle
onMounted(() => {
    loadFavorites()
})
</script>