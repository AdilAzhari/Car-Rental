<template>
  <div class="relative w-full h-full overflow-hidden rounded-xl bg-gray-100 group">
    <!-- Main Image Display -->
    <div class="relative w-full h-full">
      <transition-group
        name="slide"
        tag="div"
        class="relative w-full h-full"
      >
        <div
          v-for="(image, index) in images"
          :key="index"
          v-show="index === currentIndex"
          class="absolute inset-0 w-full h-full"
        >
          <img
            :src="getImageUrl(image)"
            :alt="`Image ${index + 1}`"
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            loading="lazy"
            @error="handleImageError"
          />
          <!-- Gradient Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
      </transition-group>

      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-100">
        <div class="flex items-center space-x-2">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="text-gray-600 font-medium">Loading...</span>
        </div>
      </div>

      <!-- No Images State -->
      <div v-if="!loading && (!images || images.length === 0)" class="absolute inset-0 flex items-center justify-center bg-gray-100">
        <div class="text-center text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <p class="text-sm font-medium">No images available</p>
        </div>
      </div>
    </div>

    <!-- Navigation Arrows -->
    <div v-if="images && images.length > 1" class="absolute inset-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
      <button
        @click="previousImage"
        :disabled="currentIndex === 0 && !infinite"
        class="nav-arrow nav-arrow-left"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <button
        @click="nextImage"
        :disabled="currentIndex === images.length - 1 && !infinite"
        class="nav-arrow nav-arrow-right"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>

    <!-- Image Counter -->
    <div v-if="images && images.length > 1 && showCounter" class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm font-medium">
      {{ currentIndex + 1 }} / {{ images.length }}
    </div>

    <!-- Dots Indicator -->
    <div v-if="images && images.length > 1 && showDots" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
      <button
        v-for="(image, index) in images"
        :key="index"
        @click="goToImage(index)"
        class="dot-indicator"
        :class="{ 'dot-active': index === currentIndex }"
      ></button>
    </div>

    <!-- Fullscreen Button -->
    <div v-if="allowFullscreen" class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
      <button
        @click="openFullscreen"
        class="p-2 bg-black/50 backdrop-blur-sm text-white rounded-lg hover:bg-black/70 transition-colors duration-200"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Fullscreen Modal -->
  <teleport to="body">
    <transition name="fullscreen">
      <div
        v-if="isFullscreen"
        class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
        @click="closeFullscreen"
        @keydown.esc="closeFullscreen"
      >
        <div class="relative max-w-7xl max-h-full" @click.stop>
          <img
            :src="getImageUrl(images[currentIndex])"
            :alt="`Image ${currentIndex + 1}`"
            class="max-w-full max-h-full object-contain"
          />

          <!-- Close Button -->
          <button
            @click="closeFullscreen"
            class="absolute top-4 right-4 p-2 bg-black/50 text-white rounded-lg hover:bg-black/70 transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>

          <!-- Navigation in Fullscreen -->
          <div v-if="images.length > 1" class="absolute inset-y-0 left-0 flex items-center">
            <button @click="previousImage" class="p-4 m-4 bg-black/50 text-white rounded-lg hover:bg-black/70 transition-colors duration-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
          </div>

          <div v-if="images.length > 1" class="absolute inset-y-0 right-0 flex items-center">
            <button @click="nextImage" class="p-4 m-4 bg-black/50 text-white rounded-lg hover:bg-black/70 transition-colors duration-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  images: {
    type: Array,
    default: () => []
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  autoplayDelay: {
    type: Number,
    default: 5000
  },
  infinite: {
    type: Boolean,
    default: true
  },
  showDots: {
    type: Boolean,
    default: true
  },
  showCounter: {
    type: Boolean,
    default: false
  },
  allowFullscreen: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['imageChange', 'imageError'])

const currentIndex = ref(0)
const isFullscreen = ref(false)
let autoplayInterval = null

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  // If path already starts with /storage/, return as is
  if (imagePath.startsWith('/storage/')) {
    return imagePath
  }
  const cleanPath = imagePath.startsWith('/') ? imagePath.substring(1) : imagePath
  return `/storage/${cleanPath}`
}

const nextImage = () => {
  if (props.infinite) {
    currentIndex.value = (currentIndex.value + 1) % props.images.length
  } else if (currentIndex.value < props.images.length - 1) {
    currentIndex.value++
  }
  emit('imageChange', currentIndex.value)
}

const previousImage = () => {
  if (props.infinite) {
    currentIndex.value = currentIndex.value === 0 ? props.images.length - 1 : currentIndex.value - 1
  } else if (currentIndex.value > 0) {
    currentIndex.value--
  }
  emit('imageChange', currentIndex.value)
}

const goToImage = (index) => {
  currentIndex.value = index
  emit('imageChange', currentIndex.value)
}

const openFullscreen = () => {
  isFullscreen.value = true
  document.body.style.overflow = 'hidden'
}

const closeFullscreen = () => {
  isFullscreen.value = false
  document.body.style.overflow = ''
}

const handleImageError = (event) => {
  emit('imageError', { event, index: currentIndex.value })
}

const startAutoplay = () => {
  if (props.autoplay && props.images.length > 1) {
    autoplayInterval = setInterval(nextImage, props.autoplayDelay)
  }
}

const stopAutoplay = () => {
  if (autoplayInterval) {
    clearInterval(autoplayInterval)
    autoplayInterval = null
  }
}

// Watch for changes in autoplay prop
watch(() => props.autoplay, (newVal) => {
  if (newVal) {
    startAutoplay()
  } else {
    stopAutoplay()
  }
})

onMounted(() => {
  if (props.autoplay) {
    startAutoplay()
  }

  // Add keyboard navigation
  const handleKeydown = (event) => {
    if (event.key === 'ArrowLeft') {
      previousImage()
    } else if (event.key === 'ArrowRight') {
      nextImage()
    } else if (event.key === 'Escape' && isFullscreen.value) {
      closeFullscreen()
    }
  }

  document.addEventListener('keydown', handleKeydown)

  onUnmounted(() => {
    stopAutoplay()
    document.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
  })
})
</script>

<style scoped>
/* Slide Transitions */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

/* Navigation Arrows */
.nav-arrow {
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(4px);
  color: rgb(55, 65, 81);
  border-radius: 9999px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.nav-arrow:hover:not(:disabled) {
  background-color: white;
  color: rgb(37, 99, 235);
  transform: scale(1.1);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.nav-arrow:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Dots Indicator */
.dot-indicator {
  width: 0.625rem;
  height: 0.625rem;
  background-color: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  border-radius: 9999px;
  transition: all 0.2s ease;
}

.dot-indicator:hover {
  background-color: white;
  transform: scale(1.25);
}

.dot-active {
  background-color: white;
  transform: scale(1.25);
}

/* Fullscreen Modal */
.fullscreen-enter-active,
.fullscreen-leave-active {
  transition: all 0.3s ease-out;
}

.fullscreen-enter-from,
.fullscreen-leave-to {
  opacity: 0;
  backdrop-filter: blur(0px);
}
</style>