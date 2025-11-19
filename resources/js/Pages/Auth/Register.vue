<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <div>
                <div class="flex justify-center">
                    <svg class="w-20 h-20 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11H3v2h6v-2zm2-4H3v2h8V7zm4 0v2h6V7h-6zm2 4h4v2h-4v-2zM5 19h2v-2H5v2zm4 0h2v-2H9v2zm4 0h2v-2h-2v2zm4 0h2v-2h-2v2z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your renter account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                        Sign in
                    </a>
                </p>
            </div>

            <!-- Registration Form -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            autocomplete="name"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            :class="{'border-red-300': errors.name}"
                            placeholder="John Doe"
                        >
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            :class="{'border-red-300': errors.email}"
                            placeholder="john@example.com"
                        >
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
                    </div>

                    <!-- Phone (optional) -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Phone Number <span class="text-gray-500">(Optional)</span>
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            name="phone"
                            type="tel"
                            autocomplete="tel"
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            :class="{'border-red-300': errors.phone}"
                            placeholder="+1 (555) 123-4567"
                        >
                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.phone) ? errors.phone[0] : errors.phone }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            :class="{'border-red-300': errors.password}"
                            placeholder="••••••••"
                        >
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] || errors.password }}</p>
                        <div class="mt-2 text-xs text-gray-600">
                            <p class="font-medium mb-1">Password must contain:</p>
                            <ul class="list-disc list-inside space-y-0.5 ml-2">
                                <li>At least 8 characters</li>
                                <li>One uppercase letter (A-Z)</li>
                                <li>One lowercase letter (a-z)</li>
                                <li>One number (0-9)</li>
                                <li>One special character (@$!%*#?&)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            :class="{'border-red-300': errors.password_confirmation}"
                            placeholder="••••••••"
                        >
                        <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation }}</p>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            id="agree"
                            v-model="form.agree_terms"
                            name="agree"
                            type="checkbox"
                            required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="agree" class="font-medium text-gray-700">
                            I agree to the
                            <a href="#" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a>
                            and
                            <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Error message -->
                <div v-if="errors.general" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ errors.general }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing || !form.agree_terms"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 006 0zm-3 9a6 6 0 016 6H1a6 6 0 016-6z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        {{ processing ? 'Creating account...' : 'Create Account' }}
                    </button>
                </div>

                <!-- Benefits of Registration -->
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Why create an account?</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Easy booking management
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            View booking history
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Save favorite vehicles
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Exclusive member discounts
                        </li>
                    </ul>
                </div>
            </form>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="/cars" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Back to Car Listings
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

// Reactive state
const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    agree_terms: false
})

const errors = ref({})
const processing = ref(false)

// Methods
const submit = async () => {
    if (!form.value.agree_terms) {
        errors.value = { general: 'You must agree to the terms and conditions.' }
        return
    }

    processing.value = true
    errors.value = {}

    try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie')

        // Submit registration (phone is optional, so only send if provided)
        const data = {
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation
        }

        if (form.value.phone) {
            data.phone = form.value.phone
        }

        await axios.post('/register', data)

        // Redirect to dashboard after successful registration
        router.visit('/dashboard')
    } catch (error) {
        console.error('Registration error:', error.response) // For debugging

        if (error.response?.status === 422) {
            // Validation errors
            const validationErrors = error.response.data.errors || {}
            errors.value = validationErrors

            // If there are validation errors but no specific field errors shown, show general message
            if (Object.keys(validationErrors).length > 0 && !errors.value.general) {
                const errorMessages = Object.values(validationErrors).flat()
                if (errorMessages.length > 0) {
                    errors.value.general = errorMessages[0]
                }
            }
        } else if (error.response?.status === 419) {
            errors.value = { general: 'Session expired. Please refresh the page and try again.' }
        } else if (error.response?.status === 500) {
            errors.value = { general: 'Server error occurred. Please try again or contact support if the issue persists.' }
        } else {
            errors.value = { general: error.response?.data?.message || 'An error occurred during registration. Please try again.' }
        }

        // Scroll to top to show error message
        window.scrollTo({ top: 0, behavior: 'smooth' })
    } finally {
        processing.value = false
    }
}
</script>