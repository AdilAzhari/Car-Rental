<template>
  <!-- Mobile Overlay -->
  <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="onClose" />

  <div :class="sidebarClasses">
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
          </svg>
          <h2 class="text-lg font-semibold">Filters</h2>
          <span v-if="activeFiltersCount > 0" class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">
            {{ activeFiltersCount }}
          </span>
        </div>
        <div class="flex gap-2">
          <button v-if="activeFiltersCount > 0" @click="clearAllFilters" class="text-sm text-gray-500 hover:text-gray-700">
            Clear All
          </button>
          <button @click="onClose" class="lg:hidden text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Price Range -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
          </svg>
          <label class="font-medium">Price Range (per day)</label>
        </div>
        <div class="mb-3">
          <div class="flex justify-between text-sm text-gray-500 mb-2">
            <span>${{ filters.priceRange[0] }}</span>
            <span>${{ filters.priceRange[1] }}</span>
          </div>
          <input
            type="range"
            v-model="filters.priceRange[0]"
            :min="30"
            :max="300"
            step="10"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
            @input="updateFilter('priceRange', [Number($event.target.value), filters.priceRange[1]])"
          >
          <input
            type="range"
            v-model="filters.priceRange[1]"
            :min="30"
            :max="300"
            step="10"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
            @input="updateFilter('priceRange', [filters.priceRange[0], Number($event.target.value)])"
          >
        </div>
      </div>

      <!-- Location -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <label class="font-medium mb-3 block">Location</label>
        <select v-model="filters.location" @change="updateFilter('location', $event.target.value)" class="premium-input w-full">
          <option value="">Any location</option>
          <option value="downtown">Downtown</option>
          <option value="airport">Airport</option>
          <option value="suburb">Suburb</option>
        </select>
      </div>

      <!-- Vehicle Categories -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <label class="font-medium mb-3 block">Categories</label>
        <div class="space-y-2">
          <div v-for="category in ['economy', 'standard', 'luxury', 'suv']" :key="category" class="flex items-center space-x-2">
            <input
              :id="`category-${category}`"
              type="checkbox"
              :checked="filters.categories.includes(category)"
              @change="toggleArrayFilter('categories', category)"
              class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label :for="`category-${category}`" class="capitalize text-sm">
              {{ category }}
            </label>
          </div>
        </div>
      </div>

      <!-- Transmission -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <label class="font-medium">Transmission</label>
        </div>
        <div class="space-y-2">
          <div v-for="transmission in ['automatic', 'manual']" :key="transmission" class="flex items-center space-x-2">
            <input
              :id="`transmission-${transmission}`"
              type="checkbox"
              :checked="filters.transmission.includes(transmission)"
              @change="toggleArrayFilter('transmission', transmission)"
              class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label :for="`transmission-${transmission}`" class="capitalize text-sm">
              {{ transmission }}
            </label>
          </div>
        </div>
      </div>

      <!-- Fuel Type -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          <label class="font-medium">Fuel Type</label>
        </div>
        <div class="space-y-2">
          <div v-for="fuel in ['gasoline', 'hybrid', 'electric']" :key="fuel" class="flex items-center space-x-2">
            <input
              :id="`fuel-${fuel}`"
              type="checkbox"
              :checked="filters.fuelType.includes(fuel)"
              @change="toggleArrayFilter('fuelType', fuel)"
              class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label :for="`fuel-${fuel}`" class="capitalize text-sm">
              {{ fuel }}
            </label>
          </div>
        </div>
      </div>

      <!-- Seats -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <label class="font-medium">Number of Seats</label>
        </div>
        <div class="space-y-2">
          <div v-for="seats in ['2', '5', '7']" :key="seats" class="flex items-center space-x-2">
            <input
              :id="`seats-${seats}`"
              type="checkbox"
              :checked="filters.seats.includes(seats)"
              @change="toggleArrayFilter('seats', seats)"
              class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label :for="`seats-${seats}`" class="text-sm">
              {{ seats }} seats
            </label>
          </div>
        </div>
      </div>

      <!-- Features -->
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <label class="font-medium mb-3 block">Features</label>
        <div class="space-y-2">
          <div v-for="feature in ['Air Conditioning', 'GPS Navigation', 'Bluetooth', 'Backup Camera', 'Leather Seats']" :key="feature" class="flex items-center space-x-2">
            <input
              :id="`feature-${feature}`"
              type="checkbox"
              :checked="filters.features.includes(feature)"
              @change="toggleArrayFilter('features', feature)"
              class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label :for="`feature-${feature}`" class="text-sm">
              {{ feature }}
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  onFiltersChange: Function,
  isOpen: Boolean,
  onClose: Function
})

const filters = ref({
  priceRange: [50, 200],
  transmission: [],
  fuelType: [],
  seats: [],
  categories: [],
  features: [],
  location: ''
})

const activeFiltersCount = computed(() => {
  return Object.values(filters.value).reduce((acc, val) => {
    if (Array.isArray(val)) return acc + val.length
    if (typeof val === 'string' && val) return acc + 1
    return acc
  }, 0)
})

const sidebarClasses = computed(() => {
  return `
    fixed top-0 right-0 h-full w-80 bg-gray-50 border-l border-gray-200 z-50
    transform transition-transform duration-300 ease-in-out overflow-y-auto
    ${props.isOpen ? 'translate-x-0' : 'translate-x-full'}
    lg:relative lg:translate-x-0 lg:z-0 lg:w-full lg:border-l-0 lg:border-r lg:h-auto
  `
})

const updateFilter = (key, value) => {
  filters.value[key] = value
  props.onFiltersChange(filters.value)
}

const toggleArrayFilter = (key, value) => {
  const currentArray = filters.value[key]
  const newArray = currentArray.includes(value)
    ? currentArray.filter(item => item !== value)
    : [...currentArray, value]
  updateFilter(key, newArray)
}

const clearAllFilters = () => {
  filters.value = {
    priceRange: [50, 200],
    transmission: [],
    fuelType: [],
    seats: [],
    categories: [],
    features: [],
    location: ''
  }
  props.onFiltersChange(filters.value)
}
</script>

<style scoped>
.slider::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>