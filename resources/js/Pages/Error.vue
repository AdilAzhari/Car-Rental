<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <img
          src="/images/logo.jpg"
          alt="SENTIENTS A.I"
          class="mx-auto w-20 h-20 rounded-xl object-cover shadow-lg mb-6"
        />
        <h1 class="text-6xl font-bold mb-4" :class="statusColor">
          {{ status }}
        </h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
          {{ message }}
        </h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
          {{ description }}
        </p>

        <div class="space-x-4">
          <Link
            href="/"
            class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded transition duration-300 inline-block"
          >
            Go Home
          </Link>
          <button
            @click="goBack"
            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300"
          >
            Go Back
          </button>
        </div>

        <div class="mt-8 text-sm text-gray-500">
          <p>&copy; {{ currentYear }} SENTIENTS A.I - Car Rental System</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  status: {
    type: Number,
    required: true
  },
  message: {
    type: String,
    required: true
  }
})

const currentYear = new Date().getFullYear()

const statusColor = computed(() => {
  switch (props.status) {
    case 404:
      return 'text-amber-600'
    case 403:
      return 'text-red-600'
    case 419:
      return 'text-yellow-600'
    case 500:
      return 'text-red-600'
    case 503:
      return 'text-orange-600'
    default:
      return 'text-gray-600'
  }
})

const description = computed(() => {
  switch (props.status) {
    case 404:
      return "The page you're looking for doesn't exist. It might have been moved, deleted, or you entered the wrong URL."
    case 403:
      return "You don't have permission to access this resource. Please contact an administrator if you believe this is an error."
    case 419:
      return "Your session has expired. Please refresh the page and try again."
    case 500:
      return "We're experiencing some technical difficulties. Our team has been notified and is working on a fix."
    case 503:
      return "We're currently performing scheduled maintenance. Please check back soon."
    default:
      return "An unexpected error occurred. Please try again or contact support if the problem persists."
  }
})

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    window.location.href = '/'
  }
}
</script>