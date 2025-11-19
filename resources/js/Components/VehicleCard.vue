<template>
    <div class="vehicle-card card-hover group relative overflow-hidden backdrop-blur-sm">
        <!-- Enhanced Vehicle Image Carousel -->
        <div class="relative h-64 bg-gradient-to-br from-slate-100 to-slate-200 overflow-hidden rounded-t-xl">
            <ImageCarousel
                :images="vehicleImages"
                :autoplay="false"
                :infinite="true"
                :show-dots="vehicleImages.length > 1"
                :show-counter="vehicleImages.length > 3"
                :allow-fullscreen="true"
                class="w-full h-full"
                @image-error="handleImageError"
            />
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <!-- Status Badge -->
            <div class="absolute top-4 left-4">
                <span v-if="vehicle.is_available" class="inline-flex items-center gap-1 bg-emerald-500/90 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg backdrop-blur-sm border border-white/20">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Available
                </span>
                <span v-else class="inline-flex items-center gap-1 bg-red-500/90 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg backdrop-blur-sm border border-white/20">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Unavailable
                </span>
            </div>

            <!-- Favorite Icon -->
            <div class="absolute top-4 right-4">
                <button 
                    @click="toggleFavorite"
                    :disabled="favoriteLoading"
                    class="w-10 h-10 bg-white bg-opacity-90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:bg-opacity-100 transition-all duration-200 group disabled:opacity-50"
                >
                    <svg v-if="favoriteLoading" class="w-4 h-4 animate-spin text-gray-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 transition-colors" :class="isFavorite ? 'text-red-500 fill-current' : 'text-gray-600 group-hover:text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            </div>

            <!-- Quick View Button -->
            <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                <button class="bg-white/95 backdrop-blur-sm text-blue-600 px-4 py-2 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200 border border-white/30 hover:bg-white">
                    Quick View
                </button>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="p-6 bg-white rounded-b-xl">
            <!-- Vehicle Name & Year -->
            <div class="mb-4">
                <h3 class="font-bold text-xl text-gray-900 mb-1">
                    {{ vehicle.make }} {{ vehicle.model }}
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-blue-600 font-semibold">{{ vehicle.year }}</span>
                    <span v-if="vehicle.category" class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                        {{ vehicle.category }}
                    </span>
                </div>
            </div>

            <!-- Vehicle Specs -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <!-- Transmission -->
                <div class="flex items-center space-x-3 p-2 rounded-lg bg-slate-50 hover:bg-blue-50 transition-colors duration-200">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-800 capitalize">{{ vehicle.transmission }}</span>
                    </div>
                </div>
                
                <!-- Seats -->
                <div class="flex items-center space-x-3 p-2 rounded-lg bg-slate-50 hover:bg-emerald-50 transition-colors duration-200">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-800">{{ vehicle.seats }} seats</span>
                    </div>
                </div>

                <!-- Fuel Type -->
                <div class="flex items-center space-x-3 p-2 rounded-lg bg-slate-50 hover:bg-orange-50 transition-colors duration-200">
                    <div class="w-9 h-9 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-800 capitalize">{{ vehicle.fuel_type || 'Gasoline' }}</span>
                    </div>
                </div>

                <!-- Location -->
                <div class="flex items-center space-x-3 p-2 rounded-lg bg-slate-50 hover:bg-purple-50 transition-colors duration-200">
                    <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-800 truncate">{{ vehicle.location }}</span>
                    </div>
                </div>
            </div>

            <!-- Rating -->
            <div v-if="vehicle.average_rating > 0" class="flex items-center mb-6 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-100">
                <div class="flex text-yellow-400 mr-2">
                    <svg v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= vehicle.average_rating ? 'fill-current' : 'text-gray-300'" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-900">{{ vehicle.average_rating.toFixed(1) }}</span>
                <span class="text-sm text-gray-600 ml-1">({{ vehicle.total_reviews }} reviews)</span>
            </div>

            <!-- Price & Action -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex flex-col">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">${{ vehicle.daily_rate }}</span>
                        <span class="text-gray-600 ml-1 text-sm font-medium">/day</span>
                    </div>
                    <span class="text-xs text-gray-500">Best price guaranteed</span>
                </div>
                
                <div class="flex gap-2">
                    <button
                        @click="$emit('view-details', vehicle.id)"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-3 rounded-xl transition-all duration-300 inline-flex items-center justify-center shadow-md hover:shadow-lg"
                    >
                        <span>View Details</span>
                    </button>
                    <button
                        @click="$emit('reserve-now', vehicle.id)"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold px-4 py-3 rounded-xl transition-all duration-300 inline-flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                    >
                        <span>Reserve</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import ImageCarousel from './ImageCarousel.vue'

const props = defineProps({
    vehicle: {
        type: Object,
        required: true
    }
})

// Computed property to prepare images for carousel
const vehicleImages = computed(() => {
    const images = []

    // Add featured image
    if (props.vehicle.featured_image) {
        images.push(props.vehicle.featured_image)
    }

    // Add gallery images
    if (props.vehicle.gallery_images && Array.isArray(props.vehicle.gallery_images)) {
        images.push(...props.vehicle.gallery_images)
    }

    // Add images from images relationship
    if (props.vehicle.images && Array.isArray(props.vehicle.images)) {
        const relationshipImages = props.vehicle.images.map(img => img.image_path || img.path)
        images.push(...relationshipImages)
    }

    // Remove duplicates and falsy values
    return [...new Set(images.filter(Boolean))]
})

defineEmits(['view-details', 'reserve-now'])

const isFavorite = ref(false)
const favoriteLoading = ref(false)

const toggleFavorite = async () => {
    try {
        favoriteLoading.value = true
        
        const response = await axios.post('/api/favorites/toggle', {
            vehicle_id: props.vehicle.id
        })
        
        isFavorite.value = response.data.is_favorite
        
        // Show success message
        const message = response.data.message
        console.log(message) // You can replace this with a toast notification
        
    } catch (error) {
        if (error.response?.status === 401) {
            // User not authenticated - redirect to login or show login modal
            console.log('Please login to add favorites')
            // Emit event to parent component to handle login
            // $emit('show-login-modal') - if you want to implement this
        } else if (error.response?.status === 429) {
            console.log('Too many requests. Please wait a moment.')
        } else {
            console.error('Failed to toggle favorite:', error)
            console.log('Unable to update favorites. Please try again.')
        }
    } finally {
        favoriteLoading.value = false
    }
}

const checkFavoriteStatus = async () => {
    try {
        const response = await axios.get(`/api/favorites/${props.vehicle.id}`)
        isFavorite.value = response.data.is_favorite
    } catch (error) {
        // User not authenticated or other error, ignore
        console.log('Could not check favorite status')
    }
}

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

const imageRetryCount = ref(0)
const maxRetries = 2

const handleImageError = (event) => {
    console.log('Image failed to load:', event.target.src)

    // Try different image sources before giving up
    if (imageRetryCount.value < maxRetries) {
        imageRetryCount.value++

        // Try gallery images as fallback
        if (props.vehicle.gallery_images && props.vehicle.gallery_images.length > imageRetryCount.value - 1) {
            const fallbackImage = props.vehicle.gallery_images[imageRetryCount.value - 1]
            if (fallbackImage && fallbackImage !== event.target.src) {
                event.target.src = getImageUrl(fallbackImage)
                return
            }
        }

        // Try images relationship if available
        if (props.vehicle.images && props.vehicle.images.length > imageRetryCount.value - 1) {
            const fallbackImage = props.vehicle.images[imageRetryCount.value - 1]
            if (fallbackImage?.image_path && getImageUrl(fallbackImage.image_path) !== event.target.src) {
                event.target.src = getImageUrl(fallbackImage.image_path)
                return
            }
        }
    }

    // If all retries failed, hide the image and show placeholder
    event.target.style.display = 'none'
}

onMounted(() => {
    // Check if vehicle is favorited when component mounts
    checkFavoriteStatus()
})
</script>