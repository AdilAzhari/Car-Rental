<template>
    <AppLayout>

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-purple-900 overflow-hidden py-16">
            <div class="absolute inset-0 bg-black opacity-20"></div>
            <div class="relative container mx-auto px-4 text-center text-white">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Find Your Perfect
                    <span class="block text-blue-400">Rental Vehicle</span>
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto text-white/90">
                    Discover premium vehicles from trusted providers. Book instantly with our seamless rental experience.
                </p>
            </div>
        </section>

        <!-- Vehicles Section -->
        <section id="vehicles" class="py-16">
            <div class="container mx-auto px-4">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Desktop Filter Sidebar -->
                    <div class="hidden lg:block lg:w-80 flex-shrink-0">
                        <FilterSidebar
                            :onFiltersChange="setFilters"
                            :isOpen="true"
                            :onClose="() => {}"
                        />
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-foreground">
                                    Available Vehicles
                                </h2>
                                <p class="text-muted-foreground">
                                    {{ filteredVehicles.length }} vehicles available
                                </p>
                            </div>

                            <!-- Mobile Filter Button -->
                            <button
                                @click="setIsFilterOpen(true)"
                                class="lg:hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                                </svg>
                                Filters
                            </button>
                        </div>
                
                        <!-- Toolbar -->
                        <div class="flex items-center justify-between mb-6 p-4 bg-card rounded-lg border">
                            <div class="flex items-center gap-4">
                                <select v-model="sortBy" @change="sortVehicles" class="premium-input text-sm">
                                    <option value="featured">Featured</option>
                                    <option value="price-low">Price: Low to High</option>
                                    <option value="price-high">Price: High to Low</option>
                                    <option value="rating">Highest Rated</option>
                                    <option value="newest">Newest First</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    :class="viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    @click="setViewMode('grid')"
                                    class="p-2 rounded"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                </button>
                                <button
                                    :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    @click="setViewMode('list')"
                                    class="p-2 rounded"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>


                        <!-- Vehicles Grid -->
                        <div v-if="loading" class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
                            <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md animate-pulse">
                                <div class="h-48 bg-gray-200 rounded-t-lg"></div>
                                <div class="p-4">
                                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                                    <div class="h-8 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="filteredVehicles.length > 0" :class="`grid gap-6 ${
                            viewMode === 'grid'
                                ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3'
                                : 'grid-cols-1'
                        }`">
                            <VehicleCard
                                v-for="vehicle in filteredVehicles"
                                :key="vehicle.id"
                                :vehicle="vehicle"
                                @view-details="goToVehicleDetails"
                                @reserve-now="goToReservation"
                            />
                        </div>

                        <div v-else class="text-center py-16">
                            <div class="text-6xl mb-4">🚗</div>
                            <h3 class="text-xl font-semibold mb-2">No vehicles found</h3>
                            <p class="text-muted-foreground mb-4">
                                Try adjusting your filters to see more options.
                            </p>
                            <button
                                @click="clearFilters"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg"
                            >
                                Clear Filters
                            </button>
                        </div>

                        <!-- Load More Button (for future pagination) -->
                        <div v-if="filteredVehicles.length >= 6" class="text-center mt-12">
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium">
                                Load More Vehicles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mobile Filter Sidebar -->
        <FilterSidebar
            :onFiltersChange="setFilters"
            :isOpen="isFilterOpen"
            :onClose="() => setIsFilterOpen(false)"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import VehicleCard from '@/Components/VehicleCard.vue'
import FilterSidebar from '@/Components/FilterSidebar.vue'
import axios from 'axios'

// Reactive data
const vehicles = ref([])
const loading = ref(false)
const isFilterOpen = ref(false)
const sortBy = ref('featured')
const viewMode = ref('grid')

const filters = ref({
    priceRange: [50, 200],
    transmission: [],
    fuelType: [],
    seats: [],
    categories: [],
    features: [],
    location: ''
})

// Filter and sort vehicles
const filteredVehicles = computed(() => {
    let filtered = vehicles.value.filter(vehicle => {
        // Price filter
        if (vehicle.daily_rate < filters.value.priceRange[0] || vehicle.daily_rate > filters.value.priceRange[1]) {
            return false
        }

        // Category filter
        if (filters.value.categories.length > 0 && !filters.value.categories.includes(vehicle.category)) {
            return false
        }

        // Transmission filter
        if (filters.value.transmission.length > 0 && !filters.value.transmission.includes(vehicle.transmission)) {
            return false
        }

        // Fuel type filter
        if (filters.value.fuelType.length > 0 && !filters.value.fuelType.includes(vehicle.fuel_type)) {
            return false
        }

        // Seats filter
        if (filters.value.seats.length > 0 && !filters.value.seats.includes(vehicle.seats.toString())) {
            return false
        }

        // Location filter
        if (filters.value.location && filters.value.location !== '' && vehicle.location.toLowerCase() !== filters.value.location.toLowerCase()) {
            return false
        }

        // Features filter
        if (filters.value.features.length > 0) {
            const hasAllFeatures = filters.value.features.every(feature =>
                vehicle.features?.includes(feature)
            )
            if (!hasAllFeatures) return false
        }

        return true
    })

    // Sort vehicles
    switch (sortBy.value) {
        case 'price-low':
            filtered.sort((a, b) => a.daily_rate - b.daily_rate)
            break
        case 'price-high':
            filtered.sort((a, b) => b.daily_rate - a.daily_rate)
            break
        case 'rating':
            filtered.sort((a, b) => (b.average_rating || 0) - (a.average_rating || 0))
            break
        case 'newest':
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            break
        default:
            // Keep original order for featured
            break
    }

    return filtered
})

// Methods
const loadVehicles = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/cars')
        vehicles.value = response.data.data || response.data || []

        // Ensure each vehicle has required properties
        vehicles.value = vehicles.value.map(vehicle => ({
            ...vehicle,
            featured_image: vehicle.featured_image || null,
            gallery_images: vehicle.gallery_images || [],
            images: vehicle.images || [],
            average_rating: vehicle.average_rating || 0,
            total_reviews: vehicle.total_reviews || 0,
            features: vehicle.features || [],
            is_available: vehicle.is_available ?? true
        }))
    } catch (error) {
        console.error('Error fetching vehicles:', error)
        vehicles.value = []

        // Show user-friendly error message (you can replace with toast notification)
        if (error.response?.status === 404) {
            console.log('No vehicles found')
        } else if (error.response?.status >= 500) {
            console.log('Server error. Please try again later.')
        } else {
            console.log('Failed to load vehicles. Please check your connection.')
        }
    } finally {
        loading.value = false
    }
}

const goToVehicleDetails = (vehicleId) => {
    router.visit(`/cars/${vehicleId}`)
}

const goToReservation = (vehicleId) => {
    router.visit(`/cars/${vehicleId}/reserve`)
}

const setFilters = (newFilters) => {
    filters.value = newFilters
}

const setIsFilterOpen = (isOpen) => {
    isFilterOpen.value = isOpen
}

const setViewMode = (mode) => {
    viewMode.value = mode
}

const sortVehicles = () => {
    // Sorting is handled in the computed property
}

const clearFilters = () => {
    filters.value = {
        priceRange: [50, 200],
        transmission: [],
        fuelType: [],
        seats: [],
        categories: [],
        features: [],
        location: ''
    }
}

// Lifecycle
onMounted(() => {
    loadVehicles()
})
</script>