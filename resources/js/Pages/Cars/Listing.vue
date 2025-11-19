<template>
  <AppLayout>
    <!-- Header -->
    <section class="bg-gradient-to-br from-secondary-900 via-primary-800 to-primary-900 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-display font-bold mb-6 animate-fade-in">
            Premium Car Collection
          </h1>
          <p class="text-xl text-primary-100 max-w-3xl mx-auto animate-slide-up">
            Discover our extensive fleet of luxury vehicles. From economy to premium, find the perfect car for your journey.
          </p>
        </div>
      </div>
    </section>

    <!-- Filters & Search -->
    <section class="bg-white shadow-soft border-b border-secondary-200 sticky top-16 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row gap-6 items-center">
          <!-- Search -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by make, model, or type..."
                class="w-full pl-10 pr-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-secondary-900"
                @input="applyFilters"
              >
              <svg class="absolute left-3 top-3.5 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
          </div>

          <!-- Filter Toggles -->
          <div class="flex flex-wrap gap-4">
            <!-- Price Range -->
            <div class="relative">
              <button
                @click="toggleFilter('price')"
                class="flex items-center space-x-2 px-4 py-3 bg-secondary-100 hover:bg-secondary-200 rounded-xl transition-colors duration-200"
                :class="{ 'bg-primary-100 text-primary-700': activeFilters.price }"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">Price</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeFilters.price }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <transition name="filter-dropdown">
                <div v-if="activeFilters.price" class="absolute top-full mt-2 left-0 bg-white rounded-xl shadow-large border border-secondary-200 p-4 w-64 z-50">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-secondary-700 mb-2">Daily Rate Range</label>
                      <div class="flex items-center space-x-3">
                        <input
                          v-model="filters.minPrice"
                          type="number"
                          placeholder="Min"
                          class="flex-1 px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          @input="applyFilters"
                        >
                        <span class="text-secondary-500">to</span>
                        <input
                          v-model="filters.maxPrice"
                          type="number"
                          placeholder="Max"
                          class="flex-1 px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          @input="applyFilters"
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <!-- Vehicle Type -->
            <div class="relative">
              <button
                @click="toggleFilter('type')"
                class="flex items-center space-x-2 px-4 py-3 bg-secondary-100 hover:bg-secondary-200 rounded-xl transition-colors duration-200"
                :class="{ 'bg-primary-100 text-primary-700': activeFilters.type }"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v2a1 1 0 01-1 1h-1v10a1 1 0 01-1 1H6a1 1 0 01-1-1V8H4a1 1 0 01-1-1V5a1 1 0 011-1h3z"/>
                </svg>
                <span class="font-medium">Type</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeFilters.type }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <transition name="filter-dropdown">
                <div v-if="activeFilters.type" class="absolute top-full mt-2 left-0 bg-white rounded-xl shadow-large border border-secondary-200 p-4 w-48 z-50">
                  <div class="space-y-2">
                    <label v-for="type in vehicleTypes" :key="type" class="flex items-center">
                      <input
                        v-model="filters.types"
                        :value="type"
                        type="checkbox"
                        class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                        @change="applyFilters"
                      >
                      <span class="ml-2 text-sm text-secondary-700">{{ type }}</span>
                    </label>
                  </div>
                </div>
              </transition>
            </div>

            <!-- Seats -->
            <div class="relative">
              <button
                @click="toggleFilter('seats')"
                class="flex items-center space-x-2 px-4 py-3 bg-secondary-100 hover:bg-secondary-200 rounded-xl transition-colors duration-200"
                :class="{ 'bg-primary-100 text-primary-700': activeFilters.seats }"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="font-medium">Seats</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeFilters.seats }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <transition name="filter-dropdown">
                <div v-if="activeFilters.seats" class="absolute top-full mt-2 left-0 bg-white rounded-xl shadow-large border border-secondary-200 p-4 w-48 z-50">
                  <div class="space-y-2">
                    <label v-for="seat in seatOptions" :key="seat" class="flex items-center">
                      <input
                        v-model="filters.seats"
                        :value="seat"
                        type="checkbox"
                        class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                        @change="applyFilters"
                      >
                      <span class="ml-2 text-sm text-secondary-700">{{ seat }} seats</span>
                    </label>
                  </div>
                </div>
              </transition>
            </div>

            <!-- Sort -->
            <select
              v-model="filters.sort"
              @change="applyFilters"
              class="px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white text-secondary-900"
            >
              <option value="newest">Newest First</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>

          <!-- Clear Filters -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="text-primary-600 hover:text-primary-700 font-medium transition-colors duration-200"
          >
            Clear All
          </button>
        </div>
      </div>
    </section>

    <!-- Results -->
    <section class="py-12 bg-gradient-to-br from-secondary-50 to-primary-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h2 class="text-2xl font-bold text-secondary-900">
              {{ filteredCars.length }} {{ filteredCars.length === 1 ? 'Vehicle' : 'Vehicles' }} Available
            </h2>
            <p class="text-secondary-600 mt-1">Find your perfect ride from our premium collection</p>
          </div>

          <!-- View Toggle -->
          <div class="flex items-center space-x-2 bg-white rounded-xl p-1 shadow-soft">
            <button
              @click="viewMode = 'grid'"
              class="p-2 rounded-lg transition-colors duration-200"
              :class="viewMode === 'grid' ? 'bg-primary-500 text-white' : 'text-secondary-600 hover:text-primary-600'"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              </svg>
            </button>
            <button
              @click="viewMode = 'list'"
              class="p-2 rounded-lg transition-colors duration-200"
              :class="viewMode === 'list' ? 'bg-primary-500 text-white' : 'text-secondary-600 hover:text-primary-600'"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Cars Grid/List -->
        <div v-if="filteredCars.length > 0">
          <!-- Grid View -->
          <div
            v-if="viewMode === 'grid'"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
          >
            <div
              v-for="(car, index) in filteredCars"
              :key="car.id"
              class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 transform hover:-translate-y-2 overflow-hidden animate-slide-up"
              :style="{ 'animation-delay': `${index * 0.05}s` }"
            >
              <div class="relative h-48 bg-gradient-to-br from-secondary-100 to-secondary-200">
                <img
                  v-if="car.featured_image"
                  :src="car.featured_image"
                  :alt="car.make + ' ' + car.model"
                  class="w-full h-full object-cover"
                />
                <div v-else class="flex items-center justify-center h-full">
                  <svg class="w-16 h-16 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v2a1 1 0 01-1 1h-1v10a1 1 0 01-1 1H6a1 1 0 01-1-1V8H4a1 1 0 01-1-1V5a1 1 0 011-1h3z"/>
                  </svg>
                </div>
                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                  <span class="text-sm font-semibold text-primary-600">${{ car.daily_rate }}/day</span>
                </div>
              </div>

              <div class="p-6">
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">
                  {{ car.make }} {{ car.model }}
                </h3>
                <p class="text-secondary-600 mb-4">{{ car.year }}</p>

                <div class="flex items-center justify-between text-sm text-secondary-500 mb-4">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ car.seats }} seats
                  </span>
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    {{ car.transmission }}
                  </span>
                </div>

                <Link
                  :href="`/cars/${car.id}`"
                  class="block w-full bg-gradient-to-r from-primary-500 to-primary-600 text-white text-center py-3 rounded-xl font-semibold hover:from-primary-600 hover:to-primary-700 transition-all duration-200 transform hover:scale-105"
                >
                  View Details
                </Link>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else class="space-y-6">
            <div
              v-for="(car, index) in filteredCars"
              :key="car.id"
              class="bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 overflow-hidden animate-slide-up"
              :style="{ 'animation-delay': `${index * 0.05}s` }"
            >
              <div class="flex flex-col md:flex-row">
                <div class="relative w-full md:w-80 h-48 bg-gradient-to-br from-secondary-100 to-secondary-200">
                  <img
                    v-if="car.featured_image"
                    :src="car.featured_image"
                    :alt="car.make + ' ' + car.model"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="flex items-center justify-center h-full">
                    <svg class="w-16 h-16 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v2a1 1 0 01-1 1h-1v10a1 1 0 01-1 1H6a1 1 0 01-1-1V8H4a1 1 0 01-1-1V5a1 1 0 011-1h3z"/>
                    </svg>
                  </div>
                </div>

                <div class="flex-1 p-6">
                  <div class="flex justify-between items-start mb-4">
                    <div>
                      <h3 class="text-xl font-semibold text-secondary-900 mb-1">
                        {{ car.make }} {{ car.model }}
                      </h3>
                      <p class="text-secondary-600">{{ car.year }}</p>
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold text-primary-600">${{ car.daily_rate }}</div>
                      <div class="text-sm text-secondary-500">per day</div>
                    </div>
                  </div>

                  <div class="flex items-center space-x-6 text-sm text-secondary-500 mb-6">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                      </svg>
                      {{ car.seats }} seats
                    </span>
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                      </svg>
                      {{ car.transmission }}
                    </span>
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      {{ car.location || 'Multiple Locations' }}
                    </span>
                  </div>

                  <Link
                    :href="`/cars/${car.id}`"
                    class="inline-flex items-center bg-gradient-to-r from-primary-500 to-primary-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-primary-600 hover:to-primary-700 transition-all duration-200 transform hover:scale-105"
                  >
                    View Details & Book
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-16">
          <div class="max-w-md mx-auto">
            <svg class="w-24 h-24 mx-auto text-secondary-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <h3 class="text-xl font-semibold text-secondary-900 mb-2">No vehicles found</h3>
            <p class="text-secondary-600 mb-6">Try adjusting your filters or search criteria to find available vehicles.</p>
            <button
              @click="clearFilters"
              class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-primary-600 hover:to-primary-700 transition-all duration-200"
            >
              Clear All Filters
            </button>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Props
const props = defineProps({
  cars: {
    type: Object,
    required: true
  }
})

// Reactive data
const viewMode = ref('grid')
const activeFilters = ref({
  price: false,
  type: false,
  seats: false
})

const filters = ref({
  search: '',
  minPrice: '',
  maxPrice: '',
  types: [],
  seats: [],
  sort: 'newest'
})

// Data
const vehicleTypes = ['Sedan', 'SUV', 'Hatchback', 'Convertible', 'Coupe', 'Truck', 'Van']
const seatOptions = [2, 4, 5, 7, 8]

// Computed
const hasActiveFilters = computed(() => {
  return filters.value.search ||
         filters.value.minPrice ||
         filters.value.maxPrice ||
         filters.value.types.length > 0 ||
         filters.value.seats.length > 0
})

const filteredCars = computed(() => {
  let result = [...props.cars.data]

  // Search filter
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(car =>
      car.make.toLowerCase().includes(search) ||
      car.model.toLowerCase().includes(search) ||
      (car.category && car.category.toLowerCase().includes(search))
    )
  }

  // Price filter
  if (filters.value.minPrice) {
    result = result.filter(car => parseFloat(car.daily_rate) >= parseFloat(filters.value.minPrice))
  }
  if (filters.value.maxPrice) {
    result = result.filter(car => parseFloat(car.daily_rate) <= parseFloat(filters.value.maxPrice))
  }

  // Type filter
  if (filters.value.types.length > 0) {
    result = result.filter(car =>
      car.category && filters.value.types.includes(car.category)
    )
  }

  // Seats filter
  if (filters.value.seats.length > 0) {
    result = result.filter(car =>
      filters.value.seats.includes(car.seats)
    )
  }

  // Sort
  switch (filters.value.sort) {
    case 'price_low':
      result.sort((a, b) => parseFloat(a.daily_rate) - parseFloat(b.daily_rate))
      break
    case 'price_high':
      result.sort((a, b) => parseFloat(b.daily_rate) - parseFloat(a.daily_rate))
      break
    case 'popular':
      // Could be based on booking count or featured status
      result.sort((a, b) => (b.featured || 0) - (a.featured || 0))
      break
    case 'newest':
    default:
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      break
  }

  return result
})

// Methods
const toggleFilter = (filterName) => {
  activeFilters.value[filterName] = !activeFilters.value[filterName]

  // Close other filters
  Object.keys(activeFilters.value).forEach(key => {
    if (key !== filterName) {
      activeFilters.value[key] = false
    }
  })
}

const applyFilters = () => {
  // Filters are reactive, so they'll automatically update the computed property
}

const clearFilters = () => {
  filters.value = {
    search: '',
    minPrice: '',
    maxPrice: '',
    types: [],
    seats: [],
    sort: 'newest'
  }

  // Close all filter dropdowns
  Object.keys(activeFilters.value).forEach(key => {
    activeFilters.value[key] = false
  })
}

// Click outside to close filters
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    Object.keys(activeFilters.value).forEach(key => {
      activeFilters.value[key] = false
    })
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.filter-dropdown-enter-active,
.filter-dropdown-leave-active {
  transition: all 0.2s ease-out;
}

.filter-dropdown-enter-from,
.filter-dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}
</style>