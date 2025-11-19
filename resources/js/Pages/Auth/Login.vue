<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-900 via-primary-800 to-secondary-900 flex items-center justify-center p-4">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width%3D%2260%22 height%3D%2260%22 viewBox%3D%220 0 60 60%22 xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cg fill%3D%22none%22 fill-rule%3D%22evenodd%22%3E%3Cg fill%3D%22%23ffffff%22 fill-opacity%3D%220.1%22%3E%3Ccircle cx%3D%2230%22 cy%3D%2230%22 r%3D%221%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <!-- Floating Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-primary-500/10 rounded-full filter blur-3xl animate-float"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent-500/10 rounded-full filter blur-3xl animate-float" style="animation-delay: -3s;"></div>

    <div class="relative z-10 w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8 animate-fade-in">
        <Link href="/" class="inline-flex items-center space-x-3 group">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center transform group-hover:scale-105 transition-all duration-200">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <span class="text-2xl font-display font-bold text-white">CarRent</span>
        </Link>
      </div>

      <!-- Login Card -->
      <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-large p-8 animate-slide-up">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-display font-bold text-secondary-900 mb-2">Welcome Back</h1>
          <p class="text-secondary-600">Sign in to your account to continue</p>
        </div>

        <!-- Demo Credentials -->
        <div class="bg-primary-50 border border-primary-200 rounded-xl p-4 mb-6">
          <div class="flex items-center space-x-2 mb-2">
            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-primary-800">Demo Credentials</span>
          </div>
          <div class="space-y-1 text-xs text-primary-700">
            <p><strong>Email:</strong> renter@example.com</p>
            <p><strong>Password:</strong> password</p>
          </div>
          <button
            @click="fillDemoCredentials"
            class="mt-2 text-xs text-primary-600 hover:text-primary-800 font-medium underline"
          >
            Click to fill automatically
          </button>
        </div>

        <!-- Error Messages -->
        <div v-if="hasErrors" class="bg-danger-50 border border-danger-200 rounded-xl p-4 mb-6 animate-shake">
          <div class="flex items-center space-x-2 mb-2">
            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-danger-800">Please fix the following errors:</span>
          </div>
          <ul class="space-y-1 text-xs text-danger-700">
            <li v-for="(error, field) in errors" :key="field">
              <strong>{{ formatFieldName(field) }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
            </li>
          </ul>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Email -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-secondary-700">Email Address</label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="input-modern pl-10"
                :class="{ 'border-danger-300 focus:border-danger-500 focus:ring-danger-500': errors.email }"
                placeholder="Enter your email"
              >
              <svg class="absolute left-3 top-3.5 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
              </svg>
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-secondary-700">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="input-modern pl-10 pr-10"
                :class="{ 'border-danger-300 focus:border-danger-500 focus:ring-danger-500': errors.password }"
                placeholder="Enter your password"
              >
              <svg class="absolute left-3 top-3.5 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-3.5 text-secondary-400 hover:text-secondary-600 transition-colors duration-200"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center">
              <input
                v-model="form.remember"
                type="checkbox"
                class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
              >
              <span class="ml-2 text-sm text-secondary-600">Remember me</span>
            </label>
            <Link href="/forgot-password" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
              Forgot password?
            </Link>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="processing"
            class="w-full btn-primary"
            :class="{ 'opacity-50 cursor-not-allowed': processing }"
          >
            <div v-if="processing" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </div>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-secondary-200"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-secondary-500">Don't have an account?</span>
          </div>
        </div>

        <!-- Register Link -->
        <Link
          href="/register"
          class="block w-full text-center bg-secondary-100 hover:bg-secondary-200 text-secondary-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200"
        >
          Create New Account
        </Link>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <Link href="/" class="text-primary-200 hover:text-white transition-colors duration-200 text-sm">
          ← Back to Homepage
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'

// Reactive data
const form = ref({
  email: '',
  password: '',
  remember: false
})

const processing = ref(false)
const showPassword = ref(false)
const errors = ref({})

// Computed
const hasErrors = computed(() => Object.keys(errors.value).length > 0)

// Methods
const fillDemoCredentials = () => {
  form.value.email = 'renter@example.com'
  form.value.password = 'password'
}

const formatFieldName = (field) => {
  return field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ')
}

const submit = async () => {
  if (processing.value) return

  processing.value = true
  errors.value = {}

  try {
    // Get CSRF token
    await axios.get('/sanctum/csrf-cookie')

    // Attempt login
    await axios.post('/login', form.value)

    // Get intended redirect or default to dashboard
    const intended = new URLSearchParams(window.location.search).get('intended') || '/dashboard'
    router.visit(intended)

  } catch (error) {
    console.error('Login error:', error)

    if (error.response?.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
    } else if (error.response?.status === 401) {
      // Invalid credentials
      errors.value = { email: ['Invalid credentials. Please check your email and password.'] }
    } else {
      // General error
      errors.value = { general: ['An error occurred. Please try again.'] }
    }
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}

.animate-shake {
  animation: shake 0.5s ease-in-out;
}
</style>