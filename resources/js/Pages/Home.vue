<template>
  <AppLayout>
    <!-- Enhanced Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
      <!-- Background Carousel -->
      <div class="absolute inset-0 z-0">
        <ImageCarousel
          :images="heroImages"
          :autoplay="true"
          :autoplay-delay="6000"
          :infinite="true"
          :show-dots="false"
          :show-counter="false"
          :allow-fullscreen="false"
          class="w-full h-full"
        />
        <div class="absolute inset-0 bg-gradient-to-br from-black/50 via-black/30 to-black/60"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10 container mx-auto px-4 text-center text-white">
        <div class="animate-fade-in-up">
          <h1 class="text-6xl md:text-8xl font-black mb-8 leading-tight">
            <span class="bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent">
              {{ $t('hero.title') }}
            </span>
            <span class="block text-4xl md:text-6xl bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-500 bg-clip-text text-transparent font-bold mt-4">
              {{ $t('hero.subtitle') }}
            </span>
          </h1>
          <p class="text-xl md:text-2xl mb-12 max-w-3xl mx-auto text-white/90 leading-relaxed">
            {{ $t('hero.description') }}
          </p>
        </div>

        <!-- Search Card -->
        <div class="max-w-6xl mx-auto p-6 bg-white/95 backdrop-blur-sm shadow-premium rounded-lg mb-8">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="space-y-2">
              <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ $t('search.pickup_location') }}
              </label>
              <select v-model="searchForm.location" class="premium-input w-full">
                <option value="">{{ $t('search.select_location') }}</option>
                <option value="downtown">{{ $t('locations.downtown') }}</option>
                <option value="airport">{{ $t('locations.airport') }}</option>
                <option value="suburb">{{ $t('locations.suburb') }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $t('search.pickup_date') }}
              </label>
              <input
                v-model="searchForm.startDate"
                type="date"
                class="premium-input w-full"
                :min="today"
              />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $t('search.return_date') }}
              </label>
              <input
                v-model="searchForm.endDate"
                type="date"
                class="premium-input w-full"
                :min="searchForm.startDate || today"
              />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
                {{ $t('search.vehicle_type') }}
              </label>
              <select v-model="searchForm.vehicleType" class="premium-input w-full">
                <option value="">{{ $t('search.any_type') }}</option>
                <option value="economy">{{ $t('vehicle_types.economy') }}</option>
                <option value="standard">{{ $t('vehicle_types.standard') }}</option>
                <option value="luxury">{{ $t('vehicle_types.luxury') }}</option>
                <option value="suv">{{ $t('vehicle_types.suv') }}</option>
              </select>
            </div>
          </div>

          <button
            @click="searchCars"
            class="hero-button w-full md:w-auto px-12 text-lg h-12 rounded-lg flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            {{ $t('hero.search_vehicles') }}
          </button>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            href="/cars"
            class="hero-button px-8 py-4 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105"
          >
            {{ $t('hero.browse_all') }}
          </Link>
          <button
            @click="scrollToFeatures"
            class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-lg font-semibold hover:bg-white/20 transition-all duration-200"
          >
            {{ $t('hero.learn_more') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section ref="featuresSection" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-display font-bold text-secondary-900 mb-4">
            {{ $t('features.title') }}
          </h2>
          <p class="text-xl text-secondary-600 max-w-3xl mx-auto">
            {{ $t('features.description') }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="(feature, index) in features"
            :key="index"
            class="text-center p-8 rounded-2xl bg-gradient-to-br from-white to-secondary-50 shadow-soft hover:shadow-medium transition-all duration-300 transform hover:-translate-y-2 animate-slide-up"
            :style="{ 'animation-delay': `${index * 0.1}s` }"
          >
            <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transform rotate-3 hover:rotate-0 transition-transform duration-300">
              <component :is="feature.icon" class="w-8 h-8 text-white"/>
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-4">{{ feature.title }}</h3>
            <p class="text-secondary-600 leading-relaxed">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="vehicles" class="py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
            {{ $t('vehicles.why_choose_title') }}
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            {{ $t('vehicles.why_choose_description') }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
          <div class="text-center p-8 bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ $t('benefits.verified_vehicles') }}</h3>
            <p class="text-gray-600">{{ $t('benefits.verified_description') }}</p>
          </div>

          <div class="text-center p-8 bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ $t('benefits.best_prices') }}</h3>
            <p class="text-gray-600">{{ $t('benefits.prices_description') }}</p>
          </div>

          <div class="text-center p-8 bg-white rounded-2xl shadow-soft hover:shadow-medium transition-all duration-300 transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25A9.75 9.75 0 0121.75 12A9.75 9.75 0 0112 21.75 9.75 9.75 0 012.25 12A9.75 9.75 0 0112 2.25zm0 0V12m0 0h9.75"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ $t('benefits.instant_booking') }}</h3>
            <p class="text-gray-600">{{ $t('benefits.booking_description') }}</p>
          </div>
        </div>

        <div class="text-center">
          <Link
            href="/cars"
            class="inline-flex items-center bg-gradient-to-r from-primary-500 to-primary-600 text-white px-8 py-4 rounded-xl font-semibold hover:from-primary-600 hover:to-primary-700 transition-all duration-200 transform hover:scale-105 shadow-colored"
          >
            {{ $t('vehicles.explore_fleet') }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
          </Link>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-secondary-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div
            v-for="(stat, index) in stats"
            :key="index"
            class="animate-slide-up"
            :style="{ 'animation-delay': `${index * 0.1}s` }"
          >
            <div class="text-4xl md:text-5xl font-bold text-accent-400 mb-2">{{ stat.value }}</div>
            <div class="text-primary-200 font-medium">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary-600 to-primary-700 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl md:text-5xl font-display font-bold mb-6">
          {{ $t('cta.ready_title') }}
        </h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
          {{ $t('cta.ready_description') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            href="/cars"
            class="bg-white text-primary-600 px-8 py-4 rounded-xl font-semibold hover:bg-primary-50 transition-all duration-200 transform hover:scale-105 shadow-soft"
          >
            {{ $t('cta.browse_now') }}
          </Link>
          <Link
            href="/register"
            class="bg-accent-500 text-white px-8 py-4 rounded-xl font-semibold hover:bg-accent-600 transition-all duration-200 transform hover:scale-105"
          >
            {{ $t('cta.create_account') }}
          </Link>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import ImageCarousel from '@/Components/ImageCarousel.vue'

// Initialize i18n
const { t } = useI18n()

// No props needed since we removed car listing functionality

// Reactive data
const searchForm = ref({
  location: '',
  startDate: '',
  endDate: '',
  vehicleType: ''
})

const featuresSection = ref(null)

// Computed
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Removed popularCars computed since we no longer show car listings on home page

// Hero images for background carousel
const heroImages = [
  'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
  'https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
  'https://images.unsplash.com/photo-1555215695-3004980ad54e?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
  'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
  'https://images.unsplash.com/photo-1583121274602-3e2820c69888?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
]

// Data
const features = computed(() => [
  {
    title: t('features.premium_fleet.title'),
    description: t('features.premium_fleet.description'),
    icon: 'CarIcon'
  },
  {
    title: t('features.support_24_7.title'),
    description: t('features.support_24_7.description'),
    icon: 'SupportIcon'
  },
  {
    title: t('features.best_prices.title'),
    description: t('features.best_prices.description'),
    icon: 'PriceIcon'
  }
])

const stats = computed(() => [
  { value: '10K+', label: t('stats.happy_customers') },
  { value: '500+', label: t('stats.premium_cars') },
  { value: '50+', label: t('stats.locations') },
  { value: '24/7', label: t('stats.support') }
])

// Icon components
const CarIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v2a1 1 0 01-1 1h-1v10a1 1 0 01-1 1H6a1 1 0 01-1-1V8H4a1 1 0 01-1-1V5a1 1 0 011-1h3z"/>
    </svg>
  `
}

const SupportIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25A9.75 9.75 0 0121.75 12A9.75 9.75 0 0112 21.75 9.75 9.75 0 012.25 12A9.75 9.75 0 0112 2.25zm0 0V12m0 0h9.75"/>
    </svg>
  `
}

const PriceIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  `
}

// Methods
const searchCars = () => {
  const params = new URLSearchParams()
  if (searchForm.value.location) params.append('location', searchForm.value.location)
  if (searchForm.value.startDate) params.append('start_date', searchForm.value.startDate)
  if (searchForm.value.endDate) params.append('end_date', searchForm.value.endDate)
  if (searchForm.value.vehicleType) params.append('vehicle_type', searchForm.value.vehicleType)

  // Scroll to vehicles section
  const vehiclesSection = document.getElementById('vehicles')
  if (vehiclesSection) {
    vehiclesSection.scrollIntoView({ behavior: 'smooth' })
  }

  window.location.href = '/cars?' + params.toString()
}

const scrollToFeatures = () => {
  featuresSection.value?.scrollIntoView({ behavior: 'smooth' })
}

// Set default dates
onMounted(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  const dayAfter = new Date()
  dayAfter.setDate(dayAfter.getDate() + 2)

  searchForm.value.startDate = tomorrow.toISOString().split('T')[0]
  searchForm.value.endDate = dayAfter.toISOString().split('T')[0]
})
</script>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 1s ease-out;
}

/* Enhanced card styles */
.vehicle-card {
  background-color: white;
  border-radius: 1rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: all 0.5s ease;
  transform: translateY(0);
}

.vehicle-card:hover {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  transform: translateY(-0.5rem);
}

.card-hover:hover {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Premium input styles */
.premium-input {
  padding: 0.75rem 1rem;
  border: 1px solid rgb(229, 231, 235);
  border-radius: 0.75rem;
  background-color: white;
  transition: all 0.2s ease;
}

.premium-input:focus {
  outline: none;
  ring: 2px solid rgb(59, 130, 246);
  border-color: transparent;
}

/* Hero button styles */
.hero-button {
  background: linear-gradient(to right, rgb(37, 99, 235), rgb(79, 70, 229));
  color: white;
  font-weight: 600;
  transition: all 0.3s ease;
}

.hero-button:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transform: scale(1.05);
}

/* Shadow styles */
.shadow-premium {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-colored {
  box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
}
</style>